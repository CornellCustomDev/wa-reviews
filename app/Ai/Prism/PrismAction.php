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
    use PrismHistory;

    public string $userMessage = '';
    public bool $streaming = false;
    // Populated via wire:stream
    public string $streamedResponse = '';
    public bool $needsRefresh = false;
    public string $feedback = '';
    public bool $showFeedback = false;
    public array $toolsCalled = [];
    private float $streamStart;

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
        $this->startStreamTimer();
        $this->sendStreamMessage('AI request sent...');

        try {
            $finalResponse = null;

            $pendingRequest = $this->getAgent();
            $stream = $this->collectStream($pendingRequest->toRequest(), $pendingRequest->asStream());

            // Process the stream
            foreach ($stream as $message => $streamedResponse) {
                $this->sendStreamMessage($message.'... ({:elapsed}s)');
                $finalResponse = $streamedResponse;
            }
            $this->sendStreamMessage('Response received in {:elapsed}s');
            $this->userMessage = '';
            $response = $finalResponse?->toResponse();

            // Store the chat history
            $historyUlid = $this->storeHistory(
                contextModel: $this->getContextModel(),
                response: $response,
                messages: $response ? $pendingRequest->mapMessages($response) : [],
            );

            // Handle response results
            if ($response) {
                if ($response->finishReason === FinishReason::Error) {
                    $this->feedback = "**Error:** $response->text";
                    $this->showFeedback = true;
                } else {
                    $this->afterAgentResponse($response);
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
            $this->showFeedback = true;
        }

        $this->streaming = false;
        $this->needsRefresh = true;
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
                        $data['toolCalls'][] = $chunk->toolCalls[0];

                        // Add tool call message
                        $toolCallsMessage = new AssistantMessage($data['text'], $data['toolCalls']);
                        $data['messages'][] = $toolCallsMessage;
                        $pendingResponse->addResponseMessage($toolCallsMessage);

                        $toolCalled = $toolCallsMessage->toolCalls[0]->name;
                        $lastStreamMessage = $toolCalled === 'scratch_pad' ? 'Thinking' : "Using '$toolCalled'";
                        yield $lastStreamMessage => $pendingResponse;
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
