<?php

namespace App\Ai\Prism\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Prism\Prism\Providers\OpenAI\Handlers\Stream;
use Psr\Http\Message\StreamInterface;

/**
 * The Cornell gateway's SSE stream, which is not strictly OpenAI-shaped.
 *
 * The gateway is LiteLLM-fronted, so a mid-stream failure arrives as a bare
 * `{"error": {...}}` line rather than OpenAI's `{"type": "error", "error": {...}}`.
 * Prism's OpenAI handler reads `$data['type']` unguarded
 * (`Handlers/Stream.php:84`), so such a line raises `Undefined array key "type"`
 * and the real message — a context-window overflow, a bad request — is lost.
 *
 * Reshaping the line lets Prism's own error branch raise it properly; anything
 * else untyped carries no dispatchable event and is logged and skipped.
 *
 * A failure the model's own API reports arrives as a `response.failed` event
 * inside an HTTP 200 stream, with the error nested under `response`. Prism has
 * no branch for it, so the stream would simply end on its last finish reason
 * with no text, as if the model had stopped. It is reshaped the same way.
 */
class CornellStream extends Stream
{
    /**
     * @return array<string, mixed>|null
     */
    protected function parseNextDataLine(StreamInterface $stream): ?array
    {
        $data = parent::parseNextDataLine($stream);

        if (($data['type'] ?? null) === 'response.failed') {
            return [
                'type' => 'error',
                'error' => data_get($data, 'response.error') ?: ['message' => 'The model reported a failed response.'],
            ];
        }

        if ($data === null || array_key_exists('type', $data)) {
            return $data;
        }

        if (isset($data['error'])) {
            return ['type' => 'error', ...$data];
        }

        Log::warning('Cornell gateway sent an SSE data line with no type', [
            'keys' => array_keys($data),
            'raw' => Str::limit((string) json_encode($data), 2000),
        ]);

        return null;
    }
}
