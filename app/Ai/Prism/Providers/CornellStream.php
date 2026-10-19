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
 */
class CornellStream extends Stream
{
    /**
     * @return array<string, mixed>|null
     */
    protected function parseNextDataLine(StreamInterface $stream): ?array
    {
        $data = parent::parseNextDataLine($stream);

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
