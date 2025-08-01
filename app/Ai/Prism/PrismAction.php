<?php

namespace App\Ai\Prism;

use Exception;
use Generator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Enums\ChunkType;
use Prism\Prism\Enums\FinishReason;
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
                'streamedResponse' => $finalResponse?->responseMessages,
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
        $pendingResponse->addResponseMessage($userMessage);

        try {
            $lastStreamMessage = 'Awaiting response';
            foreach ($stream as $chunk) {
                switch ($chunk->chunkType) {
                    case ChunkType::ToolCall:
                        foreach ($chunk->toolCalls as $toolCall) {
                            $data['toolCalls'][] = $toolCall;
                            $toolCalled = $toolCall->name;
                            $lastStreamMessage = $toolCalled === 'scratch_pad' ? 'Thinking' : "Using '$toolCalled'";
                        }
                        // Add tool call message
                        $toolCallsMessage = new AssistantMessage($data['text'], $chunk->toolCalls);
                        $data['messages'][] = $toolCallsMessage;
                        $pendingResponse->addResponseMessage($toolCallsMessage);
                        yield $lastStreamMessage => $pendingResponse;
                        break;
                    case ChunkType::ToolResult:
                        foreach ($chunk->toolResults as $toolResult) {
                            $data['toolResults'][] = $toolResult;
                        }
                        if ($data['finish'] === FinishReason::ToolCalls) {
                            // Add tool result message
                            $toolResultsMessage = new ToolResultMessage($chunk->toolResults);
                            $data['messages'][] = $toolResultsMessage;
                            $pendingResponse->addResponseMessage($toolResultsMessage);
                            // Add the step and reset the accumulator
                            $this->addStreamedStep($data, $pendingResponse);
                            $data = $this->getStreamAccumulator($request);
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
                            yield $lastStreamMessage => $pendingResponse;

                            // Add the step and reset the accumulator
                            $this->addStreamedStep($data, $pendingResponse);
                            $data = $this->getStreamAccumulator($request);
                        }
                        break;
                    default:
                        $data['text'] .= $chunk->text ?? '';
                        yield $lastStreamMessage => $pendingResponse;
                }

                if ($chunk->finishReason) {
                    $data['finish'] = $chunk->finishReason;
                }
            }

            // In case the stream ends without storing a step
            if ($data['finish']) {
                // Add the text message
                $message = new AssistantMessage($data['text'] ?? '');
                $data['messages'][] = $message;
                $pendingResponse->addResponseMessage($message);
                // Add the step
                $this->addStreamedStep($data, $pendingResponse);
            }
        } catch (Throwable $e) {
            Log::error('PrismAction collectStream error', [
                'message' => $e->getMessage(),
                'chunkType' => $chunk->chunkType ?? null,
                'chunk' => $chunk ?? null,
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
