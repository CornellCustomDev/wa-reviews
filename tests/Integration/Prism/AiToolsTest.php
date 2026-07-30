<?php

namespace Tests\Integration\Prism;

use App\Ai\Prism\PrismAction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Facades\Tool;
use Prism\Prism\Providers\OpenAI\Maps\MessageMap;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\ToolResultMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Tests\TestCase;

class AiToolsTest extends TestCase
{
    use PrismAction;

    public static function getProviders(): array
    {
        return [
    //        'Anthropic'   => [Provider::Anthropic, 'claude-3-5-sonnet-latest'],
    //        'OpenAI'      => [Provider::OpenAI, 'gpt-4.1-mini'],
            'Cornell API' => ['cornell', 'openai.gpt-4.1-mini'],
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

        $finalResponse = null;
        foreach ($this->collectStream($pendingRequest->toRequest(), $pendingRequest->asStream()) as $streamedResponse) {
            $finalResponse = $streamedResponse;
        }

        $response = $finalResponse?->toResponse();
        $text = $response->text ?? '';

        $this->assertStringContainsString('Paris', $text);
        $this->assertStringContainsString('sunny', $text);
        $this->assertStringContainsString('72', $text);

        // The full conversation must survive the tool call step, since the
        // response messages are what get persisted to the chat history
        $messageClasses = $response->messages->map(fn ($message) => $message::class)->all();

        $this->assertSame([
            UserMessage::class,
            AssistantMessage::class,
            ToolResultMessage::class,
            AssistantMessage::class,
        ], $messageClasses);

        // Message objects, not arrays, must reach the provider MessageMap, so
        // the messages have to be pulled off the collection with all()
        $mapped = (new MessageMap($response->messages->all(), []))();

        $mappedKinds = array_map(fn (array $message) => $message['role'] ?? $message['type'], $mapped);

        $this->assertSame(
            ['user', 'function_call', 'function_call_output', 'assistant'],
            $mappedKinds,
        );
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
}
