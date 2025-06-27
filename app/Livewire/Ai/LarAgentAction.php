<?php

namespace App\Livewire\Ai;

use LarAgent\Agent;
use LarAgent\Core\Contracts\ToolCall;
use LarAgent\Messages\StreamedAssistantMessage;
use LarAgent\Messages\ToolCallMessage;
use Throwable;

trait LarAgentAction
{
    public string $userMessage = '';
    public bool $streaming = false;
    // Populated via wire:stream
    public string $streamedResponse = '';
    public bool $needsRefresh = false;
    public string $feedback = '';
    public bool $showFeedback = false;
    public array $toolsCalled = [];

    public function initiateAction(): void
    {
        // Reset state for a new action
        $this->feedback = '';
        $this->showFeedback = false;
        $this->toolsCalled = [];
        $this->streaming = true;

        // Trigger the streaming response
        try {
            $this->js('$wire.streamResponse()');
        } catch (Throwable $e) {
            $this->feedback = "**Error triggering streamResponse:** {$e->getMessage()}";
            $this->showFeedback = true;
            $this->streaming = false;
        }
    }

    public function streamResponse(): void
    {
        $this->stream('streamedResponse', 'Retrieving response...');
        $start = microtime(true);

        try {
            $agent = $this->getAgent();
            $stream = $agent->respondStreamed($this->userMessage);
            foreach ($stream as $chunk) {
                $elapsed = round(microtime(true) - $start, 1);
                if ($chunk instanceof ToolCallMessage) {
                    /** @var ToolCall $toolCall */
                    foreach ($chunk->getToolCalls() as $toolCall) {
                        $this->stream('streamedResponse', "Calling '{$toolCall->getToolName()}'... ({$elapsed}s)", true);
                    }
                }
                if ($chunk instanceof StreamedAssistantMessage) {
                    $this->stream('streamedResponse', "Retrieving response... ({$elapsed}s)", true);
                }
            }
            $elapsed = round(microtime(true) - $start, 1);
            $this->stream('streamedResponse', "Response received in $elapsed seconds.", true);
            $this->userMessage = '';

            $this->afterAgentResponse($agent);
        } catch (Throwable $e) {
            $this->feedback = "**Error:** {$e->getMessage()}";
            $this->showFeedback = true;
        }

        $this->streaming = false;
        $this->needsRefresh = true;
    }

    protected function afterAgentResponse(Agent $agent): void
    {
        //
    }
}
