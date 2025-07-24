<?php

namespace App\Ai\Prism;

use App\Models\ChatHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Prism\Prism\Text\Response;
use Symfony\Component\Uid\Ulid;

trait PrismHistory
{
    public function storeHistory(Model $contextModel, ?Response $response, array $messages): Ulid
    {
        $ulid = Str::ulid();

        ChatHistory::create([
            'ulid' => $ulid,
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

        return $ulid;
    }

}
