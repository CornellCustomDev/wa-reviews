<?php

namespace App\AiAgents\Tools;

use App\AiAgents\ReviewGuidelinesApplicabilityAgent;
use App\Models\Issue;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Illuminate\Support\Str;
use LarAgent\Tool;

class ReviewGuidelineApplicabilityTool extends Tool
{
    public string $name = 'review_guideline_applicability';

    public string $description =
        'Given an accessibility issue, a guideline number, and optionally an item representing the existing '
        . 'assessment of that guideline’s applicability, review the applicability. Returns "Applicable", '
        . '"Not Applicable", or "Uncertain" along with reasoning and optionally the applicable guideline.';

    public array $required = [
        'issue_id',
        'number',
        'item',
    ];

    public function getProperties(): array
    {
        $guidelineSchema = GuidelinesAnalyzerService::getItemsSchema();
        $guidelineSchema['type'] = ['object', 'null'];
        $guidelineSchema['description'] = 'Provided if an existing guideline is being reviewed; null otherwise.';

        return [
            'issue_id' => [
                'type' => 'integer',
                'description' => 'The primary key of the issue to analyze.',
            ],
            'number' => [
                'type' => 'string',
                'description' => 'The primary key of the guideline to analyze.',
            ],
            'item' => $guidelineSchema,
        ];
    }

    public function execute(array $input): array
    {
        $issueId = $input['issue_id'] ?? null;
        $guidelineNumber = $input['number'] ?? null;
        $item = $input['item'] ?? null;

        if (!is_numeric($issueId) || !is_string($guidelineNumber)) {
            return ['error' => 'invalid_parameters'];
        }

        $issue = Issue::findOrFail($issueId);
        if (!$issue) {
            return ['error' => 'issue_not_found'];
        }

        // TODO: Determine how we want to key these, probably somehow back to the calling chat?
        $agent = new ReviewGuidelinesApplicabilityAgent($issue, Str::ulid());

        $response = $agent->respond($agent->getContext($guidelineNumber, $item));

        return [
            'review' => $response['review'],
            'review_reasoning' => $response['review_reasoning'],
            'guideline' => $response['guideline'],
        ];
    }
}
