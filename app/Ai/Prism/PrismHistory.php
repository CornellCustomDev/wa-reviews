<?php

namespace App\Ai\Prism;

use App\Models\Agent;
use App\Models\ChatHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Prism\Prism\Text\Response;

trait PrismHistory
{
    protected ChatHistory $chatHistory;

    protected function storeHistory(Agent $agent, Model $contextModel, ?Response $response, array $messages): void
    {
        $this->chatHistory = ChatHistory::create([
            'ulid' => Str::ulid(),
            'agent_id' => $agent->getKey(),
            'user_id' => auth()->id(),
            'context_type' => get_class($contextModel),
            'context_id' => $contextModel->getKey(),
            'messages' => $response ? [
                'messages' => $messages,
                'usage' => $response->usage,
                'finish_reason' => $response->finishReason->name,
            ] : [],
            'name' => get_class($this),
        ]);
    }
}
