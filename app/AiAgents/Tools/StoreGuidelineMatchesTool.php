<?php

namespace App\AiAgents\Tools;

use App\Enums\Agents;
use App\Enums\AIStatus;
use App\Enums\Assessment;
use App\Enums\Impact;
use App\Events\ItemChanged;
use App\Models\Agent;
use App\Models\Issue;
use App\Models\Item;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Illuminate\Support\Str;
use LarAgent\Tool;

class StoreGuidelineMatchesTool extends Tool
{
    protected string $name = 'store_guideline_matches';

    protected string $description = 'Stores applicable web accessibility guidelines for an issue into the database. The guidelines are based on the Guidelines Document.';

    protected array $required = ['issue_id', 'guidelines'];

    public function getProperties(): array
    {
        return [
            'issue_id' => [
                'type' => 'integer',
                'description' => 'The primary key of the issue.',
            ],
            'guidelines' => [
                'type' => 'array',
                'items' => GuidelinesAnalyzerService::getItemsSchema(),
            ],
        ];
    }

    public function execute(array $input): array
    {
        $issueId = $input['issue_id'] ?? null;
        $guidelines = $input['guidelines'] ?? null;

        if (!is_numeric($issueId) || !is_array($guidelines)) {
            return ['error' => 'invalid_parameters'];
        }

        $issue = Issue::findOrFail($issueId);

        // Check if the user has permission to update the issue
        if (!auth()->user()->can('update', $issue)) {
            return ['status' => 'forbidden', 'feedback' => 'You do not have permission to update this issue.'];
        }

        $agent = Agent::firstWhere('name', Agents::GuidelinesAnalyzer->value);
        $feedback = [];
        $existingGuidelines = $issue->items()->pluck('guideline_id')->toArray();

        foreach ($guidelines as $guideline) {
            // Filter any guidelines that are already in the items
            if (in_array($guideline['number'], $existingGuidelines)) {
                $feedback[] = [
                    'guideline' => $guideline['number'],
                    'status' => 'already_exists',
                    'message' => 'This guideline has already been documented for the issue.',
                ];
                continue;
            }

            $item = Item::create([
                'issue_id' => $issue->id,
                'guideline_id' => $guideline['number'],
                'assessment' => Assessment::fromName($guideline['assessment']),
                'description' => Str::markdown(htmlentities($guideline['observation'])),
                'recommendation' => Str::markdown(htmlentities($guideline['recommendation'])),
                'testing' => Str::markdown(htmlentities($guideline['testing'])),
                'impact' => Impact::fromName($guideline['impact']),
                'ai_reasoning' => Str::markdown(htmlentities($guideline['reasoning'])),
                'ai_status' => AIStatus::Generated,
                'agent_id' => $agent->id,
            ]);

            event(new ItemChanged($item, 'created', $item->getAttributes(), $agent));
        }

        return ['status' => 'stored', 'feedback' => $feedback];
    }
}
