<?php

namespace App\Services\GuidelinesAnalyzer;

use App\Enums\Assessment;
use App\Models\Issue;
use App\Models\Item;
use App\Services\AzureOpenAI\ChatService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GuidelinesAnalyzerService
{
    public static function reviewIssueWithAI(Issue $issue): array
    {
        $issueContext = sprintf(
            "In the target location of \"%s\" the following issue was found: \n%s",
            $issue->target,
            $issue->description
        );

        $chat = ChatService::make();
        $chat->setPrompt(self::getGuidelinesPrompt());
        $chat->addMessage($issueContext);
        $chat->send();

        $response = json_decode($chat->getLastAiResponse());

        $results = [];
        if (isset($response->guidelines)) {
            foreach ($response->guidelines as $response) {
                $results[] = [
                    'number' => $response->number,
                    'applicability' => $response->applicability,
                    'recommendation' => $response->recommendation,
                    'testing' => $response->testing,
                ];
            }
        } elseif (isset($response->feedback)) {
            $results['feedback'] = $response->feedback;
        }

        return $results;
    }

    public static function addGuidelineItems(Issue $issue, array $result): void
    {
        foreach ($result as $guideline) {
            Item::create([
                'issue_id' => $issue->id,
                'guideline_id' => $guideline['number'],
                'description' => Str::markdown($guideline['applicability']),
                'recommendation' => Str::markdown($guideline['recommendation']),
                'testing' => Str::markdown($guideline['testing']),
                // TODO: Have reviewIssueWithAI return non-failing assessments
                'assessment' => Assessment::Fail,
            ]);
        }
    }

    public static function getGuidelinesPrompt(): string
    {
        $prompt = <<<PROMPT
As an expert in web accessibility guidelines, your task is to assist users in identifying applicable guidelines for specific web accessibility issues.

Instruction:

1. When a user presents an accessibility issue, if the issue appears to be a failure of any accessibility issues based on the Guidelines Document referenced below, provide a list named "guidelines" with an element for each relevant guideline that includes these elements:
   - number: Guideline number
   - heading: Guideline heading
   - criteria: WCAG criteria
   - applicability: Brief description of how the guideline applies to the issue
   - recommendation: Brief remediation recommendations
   - testing: Very brief testing recommendations

2. If the issue is not a direct failure of a guideline, provide a response named "feedback" with a brief explanation, including suggesting alternative resources or approaches to address the issue.

3. If you need more information about the user-provided accessibility issue to provide accurate guidance, provide a response named "feedback" asking the user for the required clarification.

Output Formatting:
  - All responses should be formatted as a JSON array, not markdown.

Example Response when Applicable Guidelines are Found:
{
  "guidelines": [
    {
      "number": "19",
      "heading": "Form input groupings (i.e., related radio buttons, related checkboxes, related text inputs like First/Last name) are grouped semantically.",
      "criteria": "1.3.1 Info and Relationships (Level A)",
      "applicability": "The fieldset must contain a <legend> or be properly labeled using ARIA to describe the grouping of checkboxes.",
      "recommendation": "Add a <legend> element to the fieldset to describe the grouping of checkboxes.",
      "testing": "Check that the grouping of checkboxes is clearly labeled using assistive technologies."
    },
    {
      "number": "61",
      "heading": "Labels describe the purpose of the inputs they are associated with.",
      "criteria": "2.4.6 Headings and Labels (Level AA)",
      "applicability": "The absence of a legend or ARIA label means that the purpose of the checkboxes is not clearly communicated to users.",
      "recommendation": "Add a clear label to each checkbox to describe its purpose.",
      "testing": "Check that each checkbox has a clear label that describes its purpose."
    }
  ]
}

Example Response when No Applicable Guidelines are Found:
{
  "feedback": "The issue you described does not appear to be a direct failure of a specific guideline. However, you can improve accessibility by ensuring that all form elements have clear labels and are properly grouped."
}

Example Response Requesting Clarification:
{
  "feedback": "To provide accurate guidance, could you please provide more information about the issue you are experiencing?"
}

Desired Outcome:
The final output should be informative and user-friendly, allowing users to easily understand the relevance and application of web accessibility guidelines in relation to their specific issues. Aim for clarity and brevity in your descriptions to facilitate quick comprehension.

The content of the Guidelines Document follows.

PROMPT;
        return $prompt . Storage::get('guidelines.md');
    }

    public static function populateIssueItemsWithAI(Issue $issue): array
    {
        $result = GuidelinesAnalyzerService::reviewIssueWithAI($issue);

        // TODO: Handle this feedback in a more meaningful way
        if (isset($result['feedback'])) {
            return $result;
        }

        if (count($result) > 0) {
            self::addGuidelineItems($issue, $result);
        }

        return [];
    }

}
