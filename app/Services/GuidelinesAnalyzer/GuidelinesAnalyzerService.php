<?php

namespace App\Services\GuidelinesAnalyzer;

use App\Enums\Agents;
use App\Enums\AIStatus;
use App\Enums\Assessment;
use App\Enums\Impact;
use App\Events\ItemChanged;
use App\Models\Agent;
use App\Models\Issue;
use App\Models\Item;
use App\Services\CornellAI\OpenAIChatService;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GuidelinesAnalyzerService
{
    public static function getAgent(): Agent
    {
        return Agent::firstWhere('name', Agents::GuidelinesAnalyzer->value);
    }

    public static function reviewIssueWithAI(Issue $issue): array
    {
        $issueContext = sprintf(
            "In the target location of \"%s\" the following issue was found: \n%s",
            $issue->target,
            $issue->description
        );

        $chat = app(OpenAIChatService::class);
        $chat->setPrompt(self::getGuidelinesPrompt());
        $chat->addMessage($issueContext);
        $chat->setResponseFormat('json_schema', self::getSchema());
        try {
            $chat->send();
        } catch (Exception $e) {
            return ['feedback' => $e->getMessage()];
        }

        $response = json_decode($chat->getLastAiResponse());

        $results = [];
        if (isset($response->guidelines)) {
            foreach ($response->guidelines as $response) {
                $results[] = [
                    'number' => $response->number,
                    'reasoning' => $response->reasoning,
                    'applicability' => $response->applicability,
                    'recommendation' => $response->recommendation,
                    'testing' => $response->testing,
                    'impact' => $response->impact,
                ];
            }
        } elseif (isset($response->feedback)) {
            $results['feedback'] = $response->feedback;
        }

        return $results;
    }

    public static function addGuidelineItems(Issue $issue, array $result): void
    {
        $agent = self::getAgent();

        foreach ($result as $guideline) {
            $item = Item::create([
                'issue_id' => $issue->id,
                'guideline_id' => $guideline['number'],
                'description' => Str::markdown($guideline['applicability']),
                'recommendation' => Str::markdown($guideline['recommendation']),
                'testing' => Str::markdown($guideline['testing']),
                // TODO: Have reviewIssueWithAI return non-failing assessments
                'assessment' => Assessment::Fail,
                'impact' => Impact::fromName($guideline['impact']),
                'ai_reasoning' => $guideline['reasoning'],
                'ai_status' => AIStatus::Generated,
                'agent_id' => $agent->id,
            ]);

            event(new ItemChanged($item, 'created', [...$item->getAttributes(), 'reasoning' => $guideline['reasoning']], $agent));
        }
    }

    public static function getGuidelinesPrompt(): string
    {
        // $testingMethods = collect(\App\Enums\TestingMethod::cases())->pluck('value')->join(', ');

        return self::getGuidelinesListPrompt() .<<<PROMPT
# Instructions

1. When an accessibility issue maps to one or more guideline failures, return a `guidelines` array containing an object for each failure with these fields:
   - reasoning: A brief explanation of how a guideline from the Guidelines Document applies to the issue, and why the impact is as stated.
   - number: The Guideline heading number from the Guidelines Document
   - heading: Guideline heading
   - criteria: WCAG criteria
   - applicability: Brief description of how the guideline is not met
   - recommendation: Brief remediation recommendations
   - testing: Very brief testing recommendations
   - impact: Rating of how significant the barrier is to users. Must be one of "Critical", "Serious", "Moderate", or "Low", as defined below:
      - Critical: A severe barrier that prevents users with affected disabilities from being able to complete primary tasks or access main content.
      - Serious: A barrier that will make task completion or content access significantly more difficult and time consuming for individuals with affected disabilities, or that may prevent affected users from completing secondary tasks or accessing supplemental content without outside support.
      - Moderate: A barrier that will make it somewhat more difficult for users with affected disabilities to complete central or secondary tasks or access content.
      - Low: A barrier that has the potential to force users with affected disabilities to use mildly inconvenient workarounds, but that does not cause much, if any, difficulty completing tasks or accessing content.(

2. If the issue is not a direct failure of a guideline, return a `feedback` string with a brief explanation (and, if helpful, alternative resources). Do not include a `guidelines` key in this case.

3. If you require more information to give accurate guidance, return a `feedback` string asking for the needed clarification. Do not include a `guidelines` key in this case.

Output Formatting:
  - All responses must conform to the provided JSON schema, include exactly one of `guidelines` or `feedback`, and must not be wrapped in markdown.

Example Response when Applicable Guidelines are Found:
{
  "guidelines": [
    {
      "reasoning": "Guideline 19 is about semantic grouping of related form inputs, such as checkboxes or radio buttons. Guideline 19 emphasizes the importance of using a <fieldset> element along with a <legend> to provide a clear description of the group. This is rated a Low impact barrier because while it may require additional effort to understand the grouping, it does not prevent users from completing tasks.",
      "number": "19",
      "heading": "Form input groupings (i.e., related radio buttons, related checkboxes, related text inputs like First/Last name) are grouped semantically.",
      "criteria": "1.3.1 Info and Relationships (Level A)",
      "applicability": "The fieldset does not contain a <legend> or be properly labeled using ARIA to describe the grouping of checkboxes.",
      "recommendation": "Add a <legend> element to the fieldset to describe the grouping of checkboxes.",
      "testing": "Check that the grouping of checkboxes is clearly labeled using assistive technologies.",
      "impact": "Low"
    },
    {
      "reasoning": "Guideline 61 is about labeling form inputs, including checkboxes. It emphasizes the importance of providing clear labels for form elements to ensure that users understand their purpose. This is rated a Serious impact barrier because the user may not understand the purpose of the checkbox, making task completion significantly more difficult.",
      "number": "61",
      "heading": "Labels describe the purpose of the inputs they are associated with.",
      "criteria": "2.4.6 Headings and Labels (Level AA)",
      "applicability": "The absence of a legend or ARIA label means that the purpose of the checkboxes is not clearly communicated to users.",
      "recommendation": "Add a clear label to each checkbox to describe its purpose.",
      "testing": "Check that each checkbox has a clear label that describes its purpose.",
      "impact": "Serious"
    }
  ],
  "feedback": null
}

Example Response when No Applicable Guidelines are Found:
{
  "guidelines": null,
  "feedback": "The issue you described does not appear to be a direct failure of a specific guideline. However, you can improve accessibility by ensuring that all form elements have clear labels and are properly grouped."
}

Example Response Requesting Clarification:
{
  "guidelines": null,
  "feedback": "To provide accurate guidance, could you please provide more information about the issue you are experiencing?"
}

Desired Outcome:
The final output should be informative and user-friendly, allowing users to easily understand the relevance and application of web accessibility guidelines in relation to their specific issues. Aim for clarity and brevity in your descriptions to facilitate quick comprehension.

PROMPT;
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

        return $result;
    }

    public static function getSchema(): array
    {
        return [
            'name' => 'guidelines_analyzer_response',
            'schema' => [
                'description' => 'Return either a "guidelines" array or a "feedback" string -- never both.',
                'type' => 'object',
                'properties' => [
                    'guidelines' => [
                        'type' => ['array', 'null'],
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'reasoning'     => ['type' => 'string'],
                                'number'        => ['type' => 'string'],
                                'heading'       => ['type' => 'string'],
                                'criteria'      => ['type' => 'string'],
                                'applicability' => ['type' => 'string'],
                                'recommendation'=> ['type' => 'string'],
                                'testing'       => ['type' => 'string'],
                                'impact'        => [
                                    'type' => 'string',
                                    'enum' => ['Critical', 'Serious', 'Moderate', 'Low'],
                                    'description' => 'Select one of the four severity levels.',
                                ],
                            ],
                            'required' => [
                                'reasoning',
                                'number',
                                'heading',
                                'criteria',
                                'applicability',
                                'recommendation',
                                'testing',
                                'impact',
                            ],
                            'additionalProperties' => false,
                        ],
                    ],
                    'feedback' => [
                        'type' => ['string', 'null'],
                        'description' => 'Used when no direct guideline failure applies or more information is needed.',
                    ],
                ],
                'additionalProperties' => false,
                'required' => ['guidelines', 'feedback'],
            ],
            'strict' => true,
        ];
    }

    public static function getGuidelinesListPrompt(): string
    {
        return <<<PROMPT
You are an expert in web accessibility guidelines. When instructions refer to the Guidelines Document, it is the
document below. When Guideline numbers are mentioned, they are the numbered sections in the Guidelines Document.

# Guidelines Document

PROMPT
            . Storage::get('guidelines-list.md') . "\n\n";
    }

}
