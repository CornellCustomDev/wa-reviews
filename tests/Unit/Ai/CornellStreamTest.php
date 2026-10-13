<?php

namespace Tests\Unit\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Streaming\Events\TextDeltaEvent;
use Tests\TestCase;

/**
 * Streams through the `cornell` provider against a faked gateway, so these
 * also fail if `CornellApi` stops using `CornellStream` or a Prism upgrade
 * stops calling the overridden parser.
 */
class CornellStreamTest extends TestCase
{
    #[Test]
    public function a_litellm_error_line_is_raised_with_the_gateways_message(): void
    {
        $this->fakeGateway(
            '{"error":{"message":"litellm.APIError: Your input exceeds the context window of this model.","type":null,"code":"400"}}',
        );

        $this->expectException(PrismException::class);
        $this->expectExceptionMessage('Code: 400. Message: litellm.APIError: Your input exceeds the context window of this model.');

        $this->streamText();
    }

    #[Test]
    public function an_untyped_line_without_an_error_is_logged_and_skipped(): void
    {
        Log::spy();
        $this->fakeGateway(
            '{"type":"response.created","response":{"id":"resp_1","model":"openai.gpt-4.1-mini"}}',
            '{"keepalive":true}',
            '{"type":"response.output_text.delta","delta":"Guideline 1.1.1"}',
            '{"type":"response.completed","response":{"id":"resp_1","usage":{"input_tokens":10,"output_tokens":3}}}',
        );

        $text = $this->streamText();

        $this->assertSame('Guideline 1.1.1', $text);
        Log::shouldHaveReceived('warning')->once()->withArgs(
            fn (string $message, array $context) => str_contains($message, 'no type')
                && $context['keys'] === ['keepalive']
        );
    }

    private function fakeGateway(string ...$jsonLines): void
    {
        $body = collect($jsonLines)->map(fn (string $json) => "data: {$json}\n\n")->implode('');

        Http::fake(['*' => Http::response($body, 200, ['Content-Type' => 'text/event-stream'])]);
    }

    private function streamText(): string
    {
        $text = '';

        foreach (Prism::text()->using('cornell', 'openai.gpt-4.1-mini')->withPrompt('Hello')->asStream() as $event) {
            if ($event instanceof TextDeltaEvent) {
                $text .= $event->delta;
            }
        }

        return $text;
    }
}
