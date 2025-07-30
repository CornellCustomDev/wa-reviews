<?php

namespace App\Ai\Prism;

use App\Models\Agent;
use App\Models\ChatHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Prism\Prism\Text\Response as TextResponse;
use Prism\Prism\Structured\Response as StructuredResponse;

trait PrismHistory
{
    protected ?ChatHistory $chatHistory;

    protected function storeChatHistory(
        Agent $agent,
        ?Model $contextModel,
        TextResponse|StructuredResponse|Null $response,
        array $messages
    ): void
    {
        $this->chatHistory = ChatHistory::create([
            'ulid' => Str::ulid(),
            'agent_id' => $agent->getKey(),
            'user_id' => auth()->id(),
            'context_type' => $contextModel ? get_class($contextModel) : 'Anonymous',
            'context_id' => $contextModel?->getKey(),
            'messages' => $response ? [
                'messages' => $messages,
                'usage' => $response->usage,
                'finish_reason' => $response->finishReason->name,
            ] : [],
            'name' => get_class($this),
        ]);
    }

    public function getChatHistory(): ?ChatHistory
    {
        return $this->chatHistory;
    }
}
