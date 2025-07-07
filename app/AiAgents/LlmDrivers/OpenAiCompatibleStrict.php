<?php

namespace App\AiAgents\LlmDrivers;

use App\AiAgents\Tools\BaseTool;
use LarAgent\Core\Contracts\Tool as ToolInterface;
use LarAgent\Core\Contracts\ToolCall as ToolCallInterface;
use LarAgent\Drivers\OpenAi\OpenAiCompatible;

/**
 * Extends OpenAiCompatible to enforce strict schema validation for tools.
 *
 *
 * See: https://platform.openai.com/docs/guides/function-calling?api-mode=chat#strict-mode
 */
class OpenAiCompatibleStrict extends OpenAiCompatible
{
    public function formatToolForPayload(ToolInterface $tool): array
    {
        $toolSchema = parent::formatToolForPayload($tool);

        if ($tool instanceof BaseTool && $tool->useStrict()) {
            if (! empty($tool->getProperties())) {
                $toolSchema['function']['strict'] = true;
                $toolSchema['function']['parameters']['additionalProperties'] = false;
            }
        }

        return $toolSchema;
    }

    public function toolResultToMessage(ToolCallInterface $toolCall, mixed $result): array
    {
        // Build toolCall message content from toolCall
        $content = json_decode($toolCall->getArguments(), true);
        $content[$toolCall->getToolName()] = $result;

        $content = $result;

        return [
            'role' => 'tool',
            'content' => json_encode($content),
            'tool_call_id' => $toolCall->getId(),
        ];
    }
}
