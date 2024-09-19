<?php

namespace App\Livewire\AI;

use App\Enums\Assessment;
use App\Models\Issue;
use App\Models\Item;
use App\Services\AzureOpenAI\ChatService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class GuidelineHelp extends Component
{
    public Issue $issue;
    public array $messages;
    public array $guidelines = [];

    public function populateGuidelines(): void
    {
        $contextMessage = sprintf(
            "In the target location of \"%s\" the following issue was found: \n%s",
            $this->issue->target,
            $this->issue->description
        );

        $chat = ChatService::make();
        $prompt = $this->getPrompt().Storage::get('guidelines.md');
        $chat->setPrompt($prompt);
        $chat->addMessage($contextMessage);
        $chat->send();
        $this->messages = $chat->getMessages();
        $response = $chat->getLastAiResponse();

        // Parse the $response json
        $response = json_decode($response);
        if (isset($response->guidelines)) {
            foreach ($response->guidelines as $response) {
                Item::create([
                    'issue_id' => $this->issue->id,
                    'guideline_id' => $response->number,
                    'description' => $response->applicability,
                    'recommendation' => $response->recommendation,
                    'assessment' => Assessment::Fail,
                    // TODO: Change testing_method to testing and make it a text field
                    //'testing_method' => substr($response->testing, 0, 255),
                ]);
            }
            $this->dispatch('issues-updated');
        } elseif (isset($response->feedback)) {
            dd($response->feedback);
        } else {
            dd($response);
        }
    }

    public function render()
    {
        return view('livewire.ai.guideline-help');
    }

    public function getPrompt(): string
    {
        return <<<PROMPT
As an expert in web accessibility guidelines, your task is to assist users in identifying applicable guidelines for specific web accessibility issues.

Instruction:

1. When a user presents an accessibility issue, if the issue appears to be a failure of any accessibility issues based on the Guidelines Document referenced below, provide a list named "guidelines" with an element for each relevant guideline that includes these elements:
   - number: Guideline number
   - heading: Guideline heading
   - criteria: WCAG criteria
   - applicability: Brief description of how the guideline applies to the issue
   - recommendation: Brief remediation recommendations
   - testing: Very brief testing recommendations

2. If the issue is not a direct failure of a guideline, provide a response named "feedback" with brief explanation and suggest alternative resources or approaches to address the issue.

3. If you need more information about the user-provided accessibility issue to provide accurate guidance, provide a response named "feedback" asking the user for clarification before providing the list of relevant guidelines.

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
    }
}
