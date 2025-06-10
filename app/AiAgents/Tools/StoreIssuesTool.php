<?php

namespace App\AiAgents\Tools;

use App\Enums\Agents;
use App\Enums\AIStatus;
use App\Enums\Assessment;
use App\Enums\Impact;
use App\Events\IssueChanged;
use App\Models\Agent;
use App\Models\Issue;
use App\Models\Scope;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Illuminate\Support\Str;
use LarAgent\Tool;

class StoreIssuesTool extends Tool
{
    protected string $name = 'store_issues';

    protected string $description = 'Stores applicable web accessibility issues into the database. The issues are based on the Guidelines Document.';

    protected array $required = ['scope_id', 'issues'];

    public static function call(int $scope_id, array $issues): array
    {
        return (new self())->execute([
            'scope_id' => $scope_id,
            'issues' => $issues,
        ]);
    }

    public function getProperties(): array
    {
        return [
            'scope_id' => [
                'type' => 'integer',
                'description' => 'The primary key of the scope.',
            ],
            'issues' => [
                'type' => 'array',
                'items' => GuidelinesAnalyzerService::getIssueSchema(),
            ],
        ];
    }

    public function execute(array $input): array
    {
        $scopeId = $input['scope_id'] ?? null;
        $issues = $input['issues'] ?? null;

        if (!is_numeric($scopeId) || !is_array($issues)) {
            return ['error' => 'invalid_parameters'];
        }

        $scope = Scope::findOrFail($scopeId);

        // Check if the user has permission to update the issue
        if (!auth()->user()->can('update', $scope)) {
            return ['status' => 'forbidden', 'feedback' => 'You do not have permission to update this issue.'];
        }

        $agent = Agent::firstWhere('name', Agents::GuidelinesAnalyzer->value);
        $feedback = [];
        $existingIssues = $scope->issues()->pluck('guideline_id')->toArray();

        foreach ($issues as $issue) {
            // Filter any issues that are already in the issues list
            if (in_array($issue['number'], $existingIssues)) {
                $feedback[] = [
                    'guideline' => $issue['number'],
                    'status' => 'already_exists',
                    'message' => 'This guideline has already been documented in an issue.',
                ];
                continue;
            }

            $issue = Issue::create([
                'project_id' => $scope->project_id,
                'scope_id' => $scope->id,
                'target' => $issue['target'],
                'guideline_id' => $issue['number'],
                'assessment' => Assessment::fromName($issue['assessment']),
                'description' => Str::markdown(htmlentities($issue['observation'])),
                'recommendation' => Str::markdown(htmlentities($issue['recommendation'])),
                'testing' => Str::markdown(htmlentities($issue['testing'])),
                'impact' => Impact::fromName($issue['impact']),
                'ai_reasoning' => Str::markdown(htmlentities($issue['reasoning'])),
                'ai_status' => AIStatus::Generated,
                'agent_id' => $agent->id,
            ]);

            event(new IssueChanged($issue, 'created', $issue->getAttributes(), $agent));
        }

        return ['status' => 'stored', 'feedback' => $feedback];
    }
}
