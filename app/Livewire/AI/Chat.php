<?php

namespace App\Livewire\AI;

use App\Services\AzureOpenAI\ChatService;
use Exception;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['sidebar' => true])]
class Chat extends Component
{
    public string $prompt;
    public array $messages = [];
    public string $userMessage = '';

    public function mount(): void
    {
        $chatService = app(ChatService::class);
        $this->prompt = $this->getPrompt();
        $this->messages = $chatService->getMessages();
    }

    public function sendMessage(): void
    {
        $chatService = app(ChatService::class);

        $chatService->setPrompt($this->prompt.Storage::get('guidelines.md'));
        $chatService->setMessages($this->messages);
        $chatService->addMessage($this->userMessage);

        try {
            $chatService->send();
            $this->userMessage = '';
        } catch (Exception $e) {
            // TODO Decide how we want to handle errors
            dd($e);
        }

        // Update the messages with the response
        $this->messages = $chatService->getMessages();
    }

    public static function getPrompt(): string
    {
        return <<<PROMPT
As an expert in web accessibility guidelines, your task is to assist users in identifying applicable guidelines for specific web accessibility issues.

Instruction:

1. When a user presents an accessibility issue, if the issue appears to be a failure of one or more accessibility issues, provide a list of relevant guidelines from the Guidelines Document referenced below, including:
   - number: Guideline number
   - heading: Guideline heading
   - criteria: WCAG criteria
   - description: A concise description of the guideline

2. If the issue is not a direct failure of a guideline, provide a brief explanation and suggest alternative resources or approaches to address the issue.

3. If you need more information about the user-provided accessibility issue to provide accurate guidance, ask the user for clarification before providing the list of relevant guidelines.

4. After presenting the list, prompt the user to specify which guidelines they would like to review.

5. For the selected guidelines, provide a list with the following information for each:
   - applicability: Brief description of how the guideline applies to the issue
   - recommendation: Brief remediation recommendations
   - testing: Very brief testing recommendations

Desired Outcome:
The final output should be informative and user-friendly, allowing users to easily understand the relevance and application of web accessibility guidelines in relation to their specific issues. Aim for clarity and brevity in your descriptions to facilitate quick comprehension.


The content of the Guidelines Document follows.

PROMPT;
    }

    public function render()
    {
        return view('livewire.ai.chat');
    }

}
