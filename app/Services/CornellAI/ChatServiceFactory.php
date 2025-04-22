<?php

namespace App\Services\CornellAI;

use App\Enums\ChatProfile;
use OpenAI;

class ChatServiceFactory implements ChatServiceFactoryInterface
{
    public function make(ChatProfile $profile): OpenAIChatService
    {
        $model = config('cornell_ai.profiles')[$profile->value]['model'];

        return match(config('cornell_ai.ai_service')) {
            ApiGatewayChatService::class => new OpenAIChatService(
                chat: OpenAI::factory()
                    ->withBaseUri(config('cornell_ai.api_gateway.base_url'))
                    ->withApiKey(config('cornell_ai.api_gateway.api_key'))
                    ->make()->chat(),
                model: $model,
            ),
            default => new OpenAIChatService(
                chat: OpenAI::client(config('cornell_ai.openai.api_key'))->chat(),
                model: $model,
            ),
        };
    }
}
