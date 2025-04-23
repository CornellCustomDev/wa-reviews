<?php

namespace App\Services\GuidelinesAnalyzer\Tools;

use App\Enums\ChatProfile;
use App\Models\Guideline;
use App\Models\Issue;
use App\Services\CornellAI\ChatServiceFactoryInterface;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerServiceInterface;
use Exception;

class ReviewGuidelineApplicability extends Tool
{
    public function __construct(
        private readonly ChatServiceFactoryInterface $chatServiceFactory,
        private readonly GuidelinesAnalyzerServiceInterface $guidelinesAnalyzerService,
    ) {}

    public function getName(): string
    {
        return 'review_guideline_applicability';
    }

    public function getDescription(): string
    {
        return 'Given an accessibility issue, a guideline number, and an item representing the existing assessment of that guideline’s applicability, review and confirm or refute the applicability. Returns "Applicable", "Not Applicable", or "Uncertain" along with reasoning.';
    }

    public function call(string $arguments): array
    {
        $arguments = json_decode($arguments, true);

        return $this->review(
            issue: Issue::findOrFail($arguments['issue_id']),
            guidelineNumber: intval($arguments['number']),
            item: $arguments['item']
        );
    }

    public function schema(): array
    {
        $itemsSchema = $this->guidelinesAnalyzerService->getItemsSchema();

        return [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'issue_id' => [
                        'type' => 'integer',
                        'description' => 'The primary key of the issue to analyze.',
                    ],
                    'number' => [
                        'type' => 'integer',
                        'description' => 'The primary key of the guideline to analyze.',
                    ],
                    'item' => $itemsSchema,
                ],
                'required' => ['issue_id', 'number', 'item'],
            ],
        ];
    }

    public function review(Issue $issue, int $guidelineNumber, array $item): array
    {
        $chat = $this->chatServiceFactory->make(ChatProfile::Chat);

        $chat->setPrompt($this->getReviewPrompt($issue, $guidelineNumber, $item));
        $chat->setResponseFormat('json_schema', $this->getResponseSchema());
        try {
            $chat->send();
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }

        $response = json_decode($chat->getLastAiResponse());

        return [
            'review' => $response->review,
            'review_reasoning' => $response->review_reasoning,
        ];
    }

    private function getResponseSchema(): array
    {
        return [
            'name' => 'review_guideline_applicability_response',
            'schema' => [
                'description' => 'Response from the AI regarding the applicability of the guideline to the issue.',
                'type' => 'object',
                'properties' => [
                    'review' => [
                        'type' => 'string',
                        'enum' => ['Applicable', 'Not Applicable', 'Uncertain'],
                    ],
                    'review_reasoning' => [
                        'type' => 'string',
                        'description' => 'A brief explanation of the reasoning behind the assessment.',
                    ],
                ],
                'additionalProperties' => false,
                'required' => ['review', 'review_reasoning'],
            ],
            'strict' => true,
        ];
    }

    private function getReviewPrompt(Issue $issue, int $guidelineNumber, array $item): string
    {
        $guideline = Guideline::with(['criterion', 'category'])->firstWhere('id', $guidelineNumber);
        $guidelineContext = json_encode($guideline, JSON_PRETTY_PRINT);
        $issueContext = $this->guidelinesAnalyzerService->getIssueContext($issue);
        $itemContext = json_encode($item, JSON_PRETTY_PRINT);

        return <<<PROMPT
# Background

You are an expert in web accessibility guidelines assisting in review of accessibility issues on web pages. You
are reviewing a specific guideline and its applicability to a web accessibility issue, both described below. The
applicability of the guideline has been assessed and you are asked to confirm or deny the applicability of the
guideline to the issue.

# Instructions

1. Review the guideline, the issue, and the current assessment of applicability.
   - The guideline is a web accessibility guideline that provides criteria for evaluating web content.
   - The issue is a specific web accessibility issue that has been identified on a web page.
   - The current assessment of applicability is the initial evaluation of whether the guideline applies to the issue.
2. If the guideline is not applicable, explain why it does not apply.
3. If the guideline is applicable, confirm it applies to the issue.
4. If it is unclear whether the guideline applies, provide a brief explanation of the uncertainty.
5. Return the response in JSON format with the following fields:
   - `review`: "Applicable", "Not Applicable", or "Uncertain"
   - `review_reasoning`: A brief explanation of the reasoning behind the assessment.

# Context: Web accessibility guideline

$guidelineContext

# Context: Web accessibility issue

$issueContext

# Context: Current assessment of applicability

$itemContext;

PROMPT;
    }
}
