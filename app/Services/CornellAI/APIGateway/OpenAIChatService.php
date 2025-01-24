<?php

namespace App\Services\CornellAI\APIGateway;

use App\Services\CornellAI\OpenAIChatService as BaseChatService;
use OpenAI;

class OpenAIChatService extends BaseChatService
{
    public function __construct(
        // Cornell AI API Gateway resource connection
        string $baseUrl,
        string $apiKey,
        string $model,
    )
    {
        $client = OpenAI::factory()
            ->withBaseUri($baseUrl)
            ->withApiKey($apiKey)
            ->make();

        parent::__construct($client->chat(), model: $model);
    }

    public static function make(): OpenAIChatService
    {
        return app(OpenAIChatService::class);
    }
}
