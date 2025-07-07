<?php

namespace Tests\Integration\Prism;

use App\Ai\Prism\PrismAction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Prism\Prism\Facades\Tool;
use Prism\Prism\Prism;
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
        foreach ($this->collectStream($pendingRequest) as $streamedResponse) {
            $finalResponse = $streamedResponse;
        }

        $text = $finalResponse->text;

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
}
