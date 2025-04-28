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
        return
            'Given an accessibility issue, a guideline number, and an optionally an item representing the existing '
            . 'assessment of that guideline’s applicability, review the applicability. Returns "Applicable", '
            . '"Not Applicable", or "Uncertain" along with reasoning and optionally the applicable guideline.';
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
        $guidelineSchema = $this->guidelinesAnalyzerService->getItemsSchema();
        $guidelineSchema['type'] = ['object', 'null'];
        $guidelineSchema['description'] = 'Provided if an existing guideline is being reviewed; null otherwise.';

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
                    'item' => $guidelineSchema,
                ],
                'required' => ['issue_id', 'number', 'item'],
            ],
        ];
    }

    public function review(Issue $issue, int $guidelineNumber, ?array $item = null): array
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
            'guideline' => $response->guideline,
        ];
    }

    private function getResponseSchema(): array
    {
        $guidelineSchema = $this->guidelinesAnalyzerService->getItemsSchema();
        $guidelineSchema['type'] = ['object', 'null'];
        $guidelineSchema['description'] = 'Filled in when the guideline is applicable; null otherwise.';

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
                    'guideline' => $guidelineSchema,
                ],
                'additionalProperties' => false,
                'required' => ['review', 'review_reasoning', 'guideline'],
            ],
            'strict' => true,
        ];
    }

    private function getReviewPrompt(Issue $issue, int $guidelineNumber, ?array $item = null): string
    {
        $guideline = Guideline::with(['criterion', 'category'])->firstWhere('id', $guidelineNumber);
        $guidelineData = $this->guidelinesAnalyzerService->mapGuidelineToSchema($guideline);
        $guidelineContext = json_encode($guidelineData, JSON_PRETTY_PRINT);
        $issueContext = $this->guidelinesAnalyzerService->getIssueContext($issue);
        $itemContext = $item ? json_encode($item, JSON_PRETTY_PRINT) : 'None provided';

        return <<<PROMPT
# Background

You are an expert in web accessibility guidelines assisting in review of accessibility issues on web pages. You
are reviewing a specific guideline and its applicability to a web accessibility issue, both described below. The
applicability of the guideline may have been assessed and you are asked to confirm or deny the applicability of the
guideline to the issue.

# Instructions

1. Review the entire guideline, the issue, and any current assessment of applicability.
   - The guideline is a web accessibility guideline that provides criteria for evaluating web content.
   - The issue is a specific web accessibility issue that has been identified on a web page.
   - The current assessment of applicability, if it is provided, is the initial evaluation of whether the guideline applies to the issue.
2. If the guideline is not applicable, explain why it does not apply.
3. If the guideline is applicable, confirm it applies to the issue.
4. If it is unclear whether the guideline applies, provide a brief explanation of the uncertainty.
5. Return the response in JSON format with the following fields:
   - `review`: "Applicable", "Not Applicable", or "Uncertain"
   - `review_reasoning`: A brief explanation of the reasoning behind the assessment.
   - `guideline`: An object with these fields, or `null` when not applicable or uncertain:
       - reasoning: Briefly explain:
          1. How the guideline applies to the issue
          2. Why it is assessed as a warning or failure
          3. Why the impact rating was chosen
       - number: The Guideline heading number from the Guidelines Document
       - heading: Guideline heading
       - criteria: WCAG criteria
       - assessment: Must be either "Fail" or "Warn":
          - Mark "Warn" if the criterion is technically met, but the implementation results in an undesirable experience for users of assistive technologies.
          - Mark "Fail" if the criterion is not met in any way, or if the user experience is significantly diminished for users of assistive technologies.
       - observation:  Briefly describe how the issue fails to meet the guideline (or why it is only a warning).
       - recommendation: Brief, actionable remediation steps.
       - testing: Very brief instructions for how to test or verify the issue.
       - impact: Rate the significance of the barrier as one of "Critical", "Serious", "Moderate", or "Low" (see definitions below). Always select the most appropriate rating based on the likely effect on users with disabilities.
          - Critical: A severe barrier that prevents users with affected disabilities from being able to complete primary tasks or access main content.
          - Serious: A barrier that will make task completion or content access significantly more difficult and time consuming for individuals with affected disabilities, or that may prevent affected users from completing secondary tasks or accessing supplemental content without outside support.
          - Moderate: A barrier that will make it somewhat more difficult for users with affected disabilities to complete central or secondary tasks or access content.
          - Low: A barrier that has the potential to force users with affected disabilities to use mildly inconvenient workarounds, but that does not cause much, if any, difficulty completing tasks or accessing content.(

## Example Responses

### Guideline is Applicable
```json
{
  "review": "Applicable",
  "review_reasoning": "Guideline 61 applies to the issue because the checkboxes are not labeled, so their purpose is not clear.",
  "guideline": {
      "reasoning": "Guideline 61 is about labeling form inputs, including checkboxes. It emphasizes the importance of providing clear labels for form elements to ensure that users understand their purpose. This is marked as a failure because the criteria requires labels for form elements and they are not present. This is rated a Serious impact barrier because the user may not understand the purpose of the checkbox, making task completion significantly more difficult.",
      "number": "61",
      "heading": "Labels describe the purpose of the inputs they are associated with.",
      "criteria": "2.4.6 Headings and Labels (Level AA)",
      "assessment": "Fail",
      "observation": "No <label> element or aria-label is programmatically associated with the checkboxes.",
      "recommendation": "Add a clear label to each checkbox to describe its purpose.",
      "testing": "Verify with a screen reader that each checkbox is announced with an appropriate label.",
      "impact": "Serious"
  }
```

### Guideline is Not Applicable
```json
{
  "review": "Not Applicable",
  "review_reasoning": "The issue concerns color contrast, which Guideline 61 does not address.",
  "guideline": null
}
```

### Applicability is Uncertain
```json
{
  "review": "Uncertain",
  "review_reasoning": "I need to see the HTML to confirm whether a programmatic label exists.",
  "guideline": null
}
```

# Context: Web accessibility guideline

$guidelineContext

# Context: Web accessibility issue

$issueContext

# Context: Current assessment of applicability

$itemContext;

PROMPT;
    }
}
