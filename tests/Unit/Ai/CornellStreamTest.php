<?php

namespace Tests\Unit\Ai;

use App\Ai\Prism\Providers\CornellStream;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\StreamInterface;
use Tests\TestCase;

class CornellStreamTest extends TestCase
{
    #[Test]
    public function it_reshapes_a_litellm_error_line_so_prism_can_raise_it(): void
    {
        $line = '{"error":{"message":"litellm.APIError: Your input exceeds the context window of this model.","type":null,"code":"400"}}';

        $data = $this->parse($line);

        // Prism's OpenAI handler dispatches on a top-level `type`; without this the
        // real message is lost to `Undefined array key "type"`.
        $this->assertSame('error', $data['type']);
        $this->assertSame('400', $data['error']['code']);
        $this->assertStringContainsString('context window', $data['error']['message']);
    }

    #[Test]
    public function it_logs_and_skips_an_untyped_line_that_carries_no_error(): void
    {
        Log::shouldReceive('warning')->once()->withArgs(
            fn (string $message, array $context) => str_contains($message, 'no type')
                && $context['keys'] === ['keepalive']
        );

        $this->assertNull($this->parse('{"keepalive":true}'));
    }

    #[Test]
    public function it_passes_a_well_formed_openai_line_through_untouched(): void
    {
        $data = $this->parse('{"type":"response.output_text.delta","delta":"Guideline 1.1.1"}');

        $this->assertSame('response.output_text.delta', $data['type']);
        $this->assertSame('Guideline 1.1.1', $data['delta']);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parse(string $json): ?array
    {
        $stream = new CornellStream(Http::baseUrl('https://example.test'));

        $method = new \ReflectionMethod($stream, 'parseNextDataLine');

        return $method->invoke($stream, $this->sse($json));
    }

    private function sse(string $json): StreamInterface
    {
        return Utils::streamFor("data: {$json}\n");
    }
}
