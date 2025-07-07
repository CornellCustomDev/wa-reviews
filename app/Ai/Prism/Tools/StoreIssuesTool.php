<?php

namespace App\Ai\Prism\Tools;

use App\Ai\Prism\PrismSchema;
use App\Enums\AIStatus;
use App\Enums\Assessment;
use App\Enums\Impact;
use App\Events\IssueChanged;
use App\Models\Issue;
use App\Models\Scope;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Illuminate\Support\Str;
use Prism\Prism\Tool;

class StoreIssuesTool extends Tool
{
    use PrismSchema;

    public function __construct()
    {
        $this->as('store_issues')
            ->for('Stores applicable web accessibility issues into the database.')
            ->withNumberParameter(
                name: 'scope_id',
                description: 'The ID of the scope to which the issues belong.'
            )
            ->withArrayParameter(
                name: 'issues',
                description: 'The issues to store.',
                items: $this->convertToPrismSchema(GuidelinesAnalyzerService::ISSUE_SCHEMA)
            )
            ->using($this);
    }

    public function __invoke(int $scope_id, array $issues): string
    {
        // Validate the scope ID and issues
        if (empty($scope_id) || empty($issues)) {
            return json_encode(['error' => 'Missing scope ID or issues']);
        }

        $scope = Scope::find($scope_id);
        if (!$scope) {
            return json_encode(['error' => 'Scope not found']);
        }

        // Check if the user has permission to update the scope
        if (!auth()->user()->can('update', $scope)) {
            return json_encode(['error' => 'You do not have permission to update this scope']);
        }

        $feedback = [];
        $existingIssues = $scope->issues()->pluck('guideline_id')->toArray();


        foreach ($issues as $issue) {
            // Sometimes the issue is a stdClass object, so convert it to an array
            $issue = (array) $issue;
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
//                'agent_id' => $agent->id,
            ]);

            event(new IssueChanged($issue, 'created', $issue->getAttributes()));
        }

        return json_encode(['status' => 'success', 'feedback' => $feedback]);
    }
}
