<?php

namespace App\Services\GuidelinesAnalyzer\Tools;

use App\Models\Issue;
use App\Models\Item;
use App\Events\ItemChanged;
use App\Enums\AIStatus;
use App\Enums\Assessment;
use App\Enums\Impact;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerServiceInterface;
use Illuminate\Support\Str;

class StoreGuidelineMatches extends Tool
{
    public function __construct(
        private readonly GuidelinesAnalyzerServiceInterface $guidelinesAnalyzerService,
    ) {}

    public function getName(): string
    {
        return 'store_guideline_matches';
    }

    public function getDescription(): string
    {
        return 'Stores web accessibility guideline matches for an issue into the database. The guidelines are based on the Guidelines Document.';
    }

    public function call(string $arguments): array
    {
        $arguments = json_decode($arguments, true);
        $issue = Issue::findOrFail($arguments['issue_id']);

        return $this->store($issue, $arguments['guidelines']);
    }

    public function schema(): array
    {
        return [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'issue_id' => [
                        'type' => 'integer',
                        'description' => 'The primary key of the issue.',
                    ],
                    'guidelines' => [
                        'type' => 'array',
                        'items' => $this->guidelinesAnalyzerService->getItemsSchema(),
                    ],
                ],
                'additionalProperties' => false,
                'required' => ['issue_id', 'guidelines'],
            ],
            'strict' => true,
        ];
    }

    public function store(Issue $issue, array $guidelines): array
    {
        $agent = $this->guidelinesAnalyzerService->getAgent();

        // Check if the user has permission to update the issue
        if (!auth()->user()->can('update', $issue)) {
            return ['status' => 'forbidden', 'feedback' => 'You do not have permission to update this issue.'];
        }

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
                'description' => Str::markdown(htmlentities($guideline['applicability'])),
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
