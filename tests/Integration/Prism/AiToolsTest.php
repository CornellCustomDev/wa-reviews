<?php

namespace Tests\Integration\Prism;

use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Prism\Prism\Enums\ChunkType;
use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Tool;
use Prism\Prism\Prism;
use Prism\Prism\Text\PendingRequest;
use Prism\Prism\Text\Response;
use Prism\Prism\Text\ResponseBuilder;
use Prism\Prism\Text\Step;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\ToolResultMessage;
use Tests\TestCase;

class AiToolsTest extends TestCase
{
    public static function getProviders(): array
    {
        return [
    //        'Anthropic'   => [Provider::Anthropic, 'claude-3-5-sonnet-latest'],
            'OpenAI'      => [Provider::OpenAI, 'gpt-4.1-mini'],
    //        'Cornell API' => ['cornell', 'openai.gpt-4.1-mini'],
        ];
    }

    #[DataProvider('getProviders')]
    #[Test]
    public function can_call_tool($provider, $model)
    {
        $weatherTool = $this->getWeatherTool();

        $response = Prism::text()
            ->using($provider, $model)
            ->withMaxSteps(2)
            ->withPrompt('What is the weather like in Paris?')
            ->withTools([$weatherTool])
            ->asText();

        $this->assertStringContainsString('Paris', $response->text);
        $this->assertStringContainsString('sunny', $response->text);
        $this->assertStringContainsString('72', $response->text);
    }

    #[DataProvider('getProviders')]
    #[Test]
    public function can_call_tool_with_streaming($provider, $model)
    {
        $weatherTool = $this->getWeatherTool();

        $pendingRequest = Prism::text()
            ->using($provider, $model)
            ->withMaxSteps(2)
            ->withPrompt('What is the weather like in Paris?')
            ->withTools([$weatherTool]);

        $streamedResponse = $this->collectStream($pendingRequest);

        $text = $streamedResponse->text;

        $this->assertStringContainsString('Paris', $text);
        $this->assertStringContainsString('sunny', $text);
        $this->assertStringContainsString('72', $text);

        // $toolResults = $response->toolResults;
    }

    private function getWeatherTool(): \Prism\Prism\Tool
    {
        return Tool::as('weather')
            ->for('Get current weather conditions')
            ->withStringParameter('city', 'The city to get weather for')
            ->using(function (string $city): string {
                return "The weather in {$city} is sunny and 72°F.";
            });
    }

    private function collectStream(PendingRequest $pendingRequest): Response
    {
        $request = $pendingRequest->toRequest();
        $stream = $pendingRequest->asStream();

        $streamedResponse = new ResponseBuilder();

        $data = $this->getStreamAccumulator();

        // Store request message
        $userMessage = Arr::last($request->messages());
        $data['messages'][] = $userMessage;
        $streamedResponse->addResponseMessage($userMessage);

        foreach ($stream as $chunk) {
            switch ($chunk->chunkType) {
                case ChunkType::ToolCall:
                    $data['toolCalls'][] = $chunk->toolCalls[0];

                    // Store tool call message
                    $toolCallsMessage = new AssistantMessage($data['text'], $data['toolCalls']);
                    $data['messages'][] = $toolCallsMessage;
                    $streamedResponse->addResponseMessage($toolCallsMessage);
                    break;
                case ChunkType::ToolResult:
                    $data['toolResults'][] = $chunk->toolResults[0];

                    if ($data['finish'] === FinishReason::ToolCalls) {
                        // Store tool result message
                        $toolResultsMessage = new ToolResultMessage($data['toolResults']);
                        $data['messages'][] = $toolResultsMessage;
                        $streamedResponse->addResponseMessage($toolResultsMessage);
                        // Store the step and reset the accumulator
                        $this->addStreamedStep($data, $streamedResponse);
                        $data = $this->getStreamAccumulator();
                    }
                    break;
                case ChunkType::Meta:
                    $data['meta'] = $chunk->meta ?? $data['meta'];
                    $data['usage'] = $chunk->usage ?? $data['usage'];

                    // If we already have a finish reason and we have usage, add the step
                    if ($data['finish'] === FinishReason::Stop && $data['usage']) {
                        // Store the text message
                        $message = new AssistantMessage($data['text']);
                        $data['messages'][] = $message;
                        $streamedResponse->addResponseMessage($message);
                        // Store the step and reset the accumulator
                        $this->addStreamedStep($data, $streamedResponse);
                        $data = $this->getStreamAccumulator();
                    }
                    break;
                default:
                    $data['text'] .= $chunk->text;
            }

            if ($chunk->finishReason) {
                $data['finish'] = $chunk->finishReason;
            }
        }

        // In case the stream ends without storing a step
        if ($data['finish']) {
            // Store the text message
            $message = new AssistantMessage($data['text']);
            $data['messages'][] = $message;
            $streamedResponse->addResponseMessage($message);
            // Store the step
            $this->addStreamedStep($data, $streamedResponse);
        }

        return $streamedResponse->toResponse();
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
