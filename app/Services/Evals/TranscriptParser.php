<?php

namespace App\Services\Evals;

use App\Ai\Prism\PrismHistory;

/**
 * Reads the stored Prism transcript shape written by
 * {@see PrismHistory::storeChatHistory()}.
 *
 * Messages come from Prism's OpenAI MessageMap, so a conversation is a flat
 * list mixing role-bearing messages with Responses-API tool entries:
 *
 * - {"role": "user", "content": [{"type": "input_text", "text": "…"}]}
 * - {"type": "function_call", "call_id": "…", "name": "…", "arguments": "{…}"}
 * - {"type": "function_call_output", "call_id": "…", "output": "…"}
 * - {"role": "assistant", "content": [{"type": "output_text", "text": "…"}]}
 */
class TranscriptParser
{
    /**
     * @param  array{messages?: array<int, mixed>, usage?: mixed, finish_reason?: string}  $stored
     */
    public function __construct(private readonly array $stored) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function messages(): array
    {
        return $this->stored['messages'] ?? [];
    }

    public function finishReason(): ?string
    {
        return $this->stored['finish_reason'] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function usage(): ?array
    {
        $usage = $this->stored['usage'] ?? null;

        return is_array($usage) ? $usage : null;
    }

    /**
     * The prompt the agent was given, i.e. the first user turn.
     */
    public function firstUserText(): ?string
    {
        foreach ($this->messages() as $message) {
            if (($message['role'] ?? null) === 'user') {
                $text = $this->contentText($message);

                if ($text !== null) {
                    return $text;
                }
            }
        }

        return null;
    }

    /**
     * The agent's final answer. Structured responses append the assistant
     * message twice, so the last one is always the complete text.
     */
    public function lastAssistantText(): ?string
    {
        foreach (array_reverse($this->messages()) as $message) {
            if (($message['role'] ?? null) === 'assistant') {
                $text = $this->contentText($message);

                if ($text !== null) {
                    return $text;
                }
            }
        }

        return null;
    }

    /**
     * @return array<int, array{name: string, arguments: mixed}>
     */
    public function toolCalls(): array
    {
        $calls = [];

        foreach ($this->messages() as $message) {
            if (($message['type'] ?? null) !== 'function_call') {
                continue;
            }

            $calls[] = [
                'name' => $message['name'] ?? 'unknown',
                'arguments' => $message['arguments'] ?? null,
            ];
        }

        return $calls;
    }

    /**
     * Distinct tool names in call order, used as a stratification tag.
     *
     * @return array<int, string>
     */
    public function toolNames(): array
    {
        return array_values(array_unique(array_column($this->toolCalls(), 'name')));
    }

    /**
     * Content may be a plain string (system messages) or a list of typed parts.
     */
    private function contentText(array $message): ?string
    {
        $content = $message['content'] ?? null;

        if (is_string($content)) {
            return $content !== '' ? $content : null;
        }

        if (! is_array($content)) {
            return null;
        }

        $parts = [];

        foreach ($content as $part) {
            if (is_string($part)) {
                $parts[] = $part;
            } elseif (isset($part['text']) && is_string($part['text'])) {
                $parts[] = $part['text'];
            }
        }

        $text = trim(implode("\n", $parts));

        return $text !== '' ? $text : null;
    }
}
