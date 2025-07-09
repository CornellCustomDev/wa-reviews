<?php

namespace App\Ai\Prism;

use Generator;
use Illuminate\Support\Arr;
use Prism\Prism\Enums\ChunkType;
use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Text\PendingRequest;
use Prism\Prism\Text\ResponseBuilder;
use Prism\Prism\Text\Step;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\ToolResultMessage;
use Throwable;

trait PrismAction
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
        } catch (\Throwable $e) {
            $this->feedback = "**Error triggering streamResponse:** {$e->getMessage()}";
            $this->showFeedback = true;
            $this->streaming = false;
        }
    }

    public function streamResponse(): void
    {
        $this->stream('streamedResponse', 'AI request sent...');
        $start = microtime(true);

        try {
            $pendingRequest = $this->getAgent();

            $generator = $this->collectStream($pendingRequest);
            $elapsed = 0.0;
            $finalResponse = null;

            // Process the generator
            foreach ($generator as $message => $streamedResponse) {
                $elapsed = number_format(microtime(true) - $start, 1, '.', '');
                $this->stream('streamedResponse', $message."... ({$elapsed}s)", true);
                $finalResponse = $streamedResponse;
            }
            $this->stream('streamedResponse', "Response received in {$elapsed}s", true);
            $this->userMessage = '';

            $this->afterAgentResponse($finalResponse);
        } catch (Throwable $e) {
            $this->feedback = "**Error:** {$e->getMessage()}";
            $this->showFeedback = true;
        }

        $this->streaming = false;
        $this->needsRefresh = true;
    }


    private function collectStream(PendingRequest $pendingRequest): Generator
    {
        $request = $pendingRequest->toRequest();
        $stream = $pendingRequest->asStream();

        $pendingResponse = new ResponseBuilder();

        $data = $this->getStreamAccumulator();

        // Start the response with the user message
        $userMessage = Arr::last($request->messages());
        $data['messages'][] = $userMessage;
        $pendingResponse->addResponseMessage($userMessage);

        $lastStreamMessage = 'Awaiting response';
        foreach ($stream as $chunk) {
            switch ($chunk->chunkType) {
                case ChunkType::ToolCall:
                    $data['toolCalls'][] = $chunk->toolCalls[0];

                    // Add tool call message
                    $toolCallsMessage = new AssistantMessage($data['text'], $data['toolCalls']);
                    $data['messages'][] = $toolCallsMessage;
                    $pendingResponse->addResponseMessage($toolCallsMessage);

                    $toolCalled = $toolCallsMessage->toolCalls[0]->name;
                    $lastStreamMessage = $toolCalled === 'scratch_pad' ? 'Thinking' : "Using '$toolCalled'";
                    yield $lastStreamMessage => null;
                    break;
                case ChunkType::ToolResult:
                    $data['toolResults'][] = $chunk->toolResults[0];

                    if ($data['finish'] === FinishReason::ToolCalls) {
                        // Add tool result message
                        $toolResultsMessage = new ToolResultMessage($data['toolResults']);
                        $data['messages'][] = $toolResultsMessage;
                        $pendingResponse->addResponseMessage($toolResultsMessage);

                        // Add the step and reset the accumulator
                        $this->addStreamedStep($data, $pendingResponse);
                        $data = $this->getStreamAccumulator();
                    }
                    break;
                case ChunkType::Meta:
                    $data['meta'] = $chunk->meta ?? $data['meta'];
                    $data['usage'] = $chunk->usage ?? $data['usage'];

                    // If we already have a finish reason and we have usage, add the step
                    if ($data['finish'] === FinishReason::Stop && $data['usage']) {
                        // Add the text message
                        $message = new AssistantMessage($data['text']);
                        $data['messages'][] = $message;
                        $pendingResponse->addResponseMessage($message);
                        $lastStreamMessage = 'Retrieving response';
                        yield $lastStreamMessage => $pendingResponse->toResponse();

                        // Add the step and reset the accumulator
                        $this->addStreamedStep($data, $pendingResponse);
                        $data = $this->getStreamAccumulator();
                    }
                    break;
                default:
                    $data['text'] .= $chunk->text;
                    yield $lastStreamMessage => null;
            }

            if ($chunk->finishReason) {
                $data['finish'] = $chunk->finishReason;
            }
        }

        // In case the stream ends without storing a step
        if ($data['finish']) {
            // Add the text message
            $message = new AssistantMessage($data['text']);
            $data['messages'][] = $message;
            $pendingResponse->addResponseMessage($message);
            // Add the step
            $this->addStreamedStep($data, $pendingResponse);
        }

        yield "Response finished." => $pendingResponse->toResponse();
    }

    private function addStreamedStep(array $current, ResponseBuilder $streamedResponse): void
    {
        $streamedResponse->addStep(
            new Step(
                text: $current['text'],
                finishReason: $current['finish'],
                toolCalls: $current['toolCalls'],
                toolResults: $current['toolResults'],
                usage: $current['usage'],
                meta: $current['meta'],
                messages: $current['messages'],
                systemPrompts: [],
            )
        );
    }

    private function getStreamAccumulator(): array
    {
        return [
            'text' => '',
            'toolCalls' => [],
            'toolResults' => [],
            'meta' => null,
            'finish' => null,
            'usage' => null,
            'messages' => [],
        ];
    }
}
