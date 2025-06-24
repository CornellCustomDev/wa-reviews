<?php

namespace App\AiAgents\Tools;

use App\Enums\Agents;
use App\Events\ItemChanged;
use App\Models\Agent;
use App\Models\Issue;
use App\Models\Item;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use LarAgent\Tool;

class StoreGuidelineMatchesTool extends Tool
{
    protected string $name = 'store_guideline_matches';

    protected string $description = 'Stores applicable web accessibility guidelines for an issue into the database. The guidelines are based on the Guidelines Document.';

    protected array $required = ['issue_id', 'guidelines'];

    public static function call(int $issue_id, array $guidelines): array
    {
        return (new self())->execute([
            'issue_id' => $issue_id,
            'guidelines' => $guidelines,
        ]);
    }

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
            // Sometimes the guideline is a stdClass object, so convert it to an array
            $guideline = (array) $guideline;
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
                ...GuidelinesAnalyzerService::mapResponseToItemArray($guideline),
                'agent_id' => $agent->id,
            ]);

            event(new ItemChanged($item, 'created', $item->getAttributes(), $agent));
        }

        return ['status' => 'stored', 'feedback' => $feedback];
    }
}
