<?php

namespace App\Ai\Prism;

use Exception;
use Generator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Enums\StreamEventType;
use Prism\Prism\Streaming\Events\StreamEvent;
use Prism\Prism\Text\Request;
use Prism\Prism\Text\ResponseBuilder;
use Prism\Prism\Text\Step;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\ToolResultMessage;
use Prism\Prism\ValueObjects\Meta;
use Prism\Prism\ValueObjects\Usage;
use Throwable;

trait PrismAction
{
    public string $userMessage = '';
    public bool $streaming = false;
    // Populated via wire:stream
    public string $streamedResponse = '';
    public string $feedback = '';
    public bool $showFeedback = false;
    private float $streamStart;

    public function initiateAction(): void
    {
        // Reset state for a new action
        $this->feedback = '';
        $this->showFeedback = false;
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
        $this->startStreamTimer();
        $this->sendStreamMessage('AI request sent...');

        try {
            $finalResponse = null;

            $actionAgent = $this->getAgent();
            $stream = $this->collectStream($actionAgent->toRequest(), $actionAgent->asStream());

            // Process the stream
            foreach ($stream as $message => $streamedResponse) {
                $this->sendStreamMessage($message.'... ({:elapsed}s)');
                $finalResponse = $streamedResponse;
            }
            $this->sendStreamMessage('Response received in {:elapsed}s');
            $this->userMessage = '';
            $response = $finalResponse?->toResponse();

            // Preserve the streamed response data
            $actionAgent->storeResponse($response);

            // Handle response results
            if ($response) {
                if ($response->finishReason === FinishReason::Error) {
                    $this->feedback = "**Error:** $response->text";
                } else {
                    $this->sendStreamMessage('Processing response... ({:elapsed}s)');
                    $this->feedback = $actionAgent->handleResponse($response) ?? '';
                }
            } else {
                throw new Exception('No response received from AI.');
            }
        } catch (Throwable $e) {
            Log::error('PrismAction streamResponse error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'stepsCollected' => $finalResponse?->steps->count(),
            ]);
            $this->feedback = "**Error:** {$e->getMessage()}";
        }

        $this->showFeedback = true;
        $this->streaming = false;
    }

    private function collectStream(Request $request, Generator $stream): Generator
    {
        $pendingResponse = new ResponseBuilder();

        $data = $this->getStreamAccumulator($request);

        // Start the response with the user message
        $userMessage = Arr::last($request->messages());
        $data['messages'][] = $userMessage;

        try {
            $lastStreamMessage = 'Awaiting response';
            /** @var StreamEvent $event */
            foreach ($stream as $event) {
                switch ($event->type()) {
                    case StreamEventType::ToolCall:
                        // One tool call per event now, rather than an array per chunk
                        $data['toolCalls'][] = $event->toolCall;
                        $toolCalled = $event->toolCall->name;
                        $lastStreamMessage = $toolCalled === 'scratch_pad' ? 'Thinking' : "Using '$toolCalled'";
                        yield $lastStreamMessage => $pendingResponse;
                        break;
                    case StreamEventType::ToolResult:
                        // One tool result per event now, rather than an array per chunk
                        $data['toolResults'][] = $event->toolResult;
                        break;
                    case StreamEventType::StepFinish:
                        // Finish reasons only arrive on StreamEnd now, so tool call steps
                        // are closed out here instead of on the tool result
                        if ($data['toolCalls']) {
                            $data['finish'] = FinishReason::ToolCalls;
                            // Add the tool call and tool result messages
                            $data['messages'][] = new AssistantMessage($data['text'], $data['toolCalls']);
                            $data['messages'][] = new ToolResultMessage($data['toolResults']);
                            // Add the step and reset the accumulator, carrying the
                            // messages forward so the final step holds the full
                            // conversation, which is what toResponse() reads
                            $this->addStreamedStep($data, $pendingResponse);
                            $messages = $data['messages'];
                            $data = $this->getStreamAccumulator($request);
                            $data['messages'] = $messages;
                        }
                        break;
                    case StreamEventType::StreamEnd:
                        // Usage and the finish reason arrive here rather than on a meta chunk
                        $data['usage'] = $event->usage ?? $data['usage'];
                        $data['finish'] = $event->finishReason;
                        $lastStreamMessage = 'Retrieving response';
                        yield $lastStreamMessage => $pendingResponse;
                        break;
                    case StreamEventType::TextDelta:
                        $data['text'] .= $event->delta;
                        yield $lastStreamMessage => $pendingResponse;
                        break;
                    case StreamEventType::Error:
                        $data['text'] = $event->message;
                        $data['finish'] = FinishReason::Error;
                        $lastStreamMessage = "Error: $event->message";
                        yield $lastStreamMessage => $pendingResponse;
                        break;
                }
            }

            // In case the stream ends without storing a step
            if ($data['finish']) {
                // toResponse() appends the final assistant message itself now
                $this->addStreamedStep($data, $pendingResponse);
            }
        } catch (Throwable $e) {
            Log::error('PrismAction collectStream error', [
                'message' => $e->getMessage(),
                'eventType' => isset($event) ? $event->type() : null,
                'event' => $event ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            $data['text'] = $e->getMessage();
            $data['finish'] = FinishReason::Error;
            $this->addStreamedStep($data, $pendingResponse);
        }

        yield "Response finished." => $pendingResponse;
    }

    private function addStreamedStep(array $current, ResponseBuilder $streamedResponse): void
    {
        $streamedResponse->addStep(
            new Step(
                text: $current['text'],
                finishReason: $current['finish'],
                toolCalls: $current['toolCalls'],
                toolResults: $current['toolResults'],
                providerToolCalls: $current['providerToolCalls'],
                usage: $current['usage'],
                meta: $current['meta'],
                messages: $current['messages'],
                systemPrompts: [],
            )
        );
    }

    private function getStreamAccumulator(Request $request): array
    {
        return [
            'text' => '',
            'toolCalls' => [],
            'toolResults' => [],
            'providerToolCalls' => [],
            'meta' => new Meta($request->provider(), $request->model()),
            'finish' => null,
            'usage' => new Usage(0, 0),
            'messages' => [],
        ];
    }

    protected function sendStreamMessage(string $streamMessage, ?bool $withElapsed = true, ?bool $replace = true): void
    {
        if ($withElapsed) {
            $elapsedTime = $this->getElapsedTime();
            $streamMessage = strtr($streamMessage, [
                '{:elapsed}' => $elapsedTime,
                ':elapsed' => $elapsedTime,
            ]);
        }

        $this->stream(
            to: 'streamedResponse',
            content: $streamMessage,
            replace: $replace,
        );
    }

    private function startStreamTimer(): void
    {
        $this->streamStart = microtime(true);
    }

    private function getElapsedTime(): string
    {
        if (!isset($this->streamStart)) {
            $this->startStreamTimer();
        }

        return number_format(microtime(true) - $this->streamStart, 1, '.', '');
    }
}
