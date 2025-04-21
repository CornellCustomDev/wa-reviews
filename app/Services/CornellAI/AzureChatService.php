<?php

namespace App\Services\CornellAI;

use App\Services\CornellAI\OpenAIChatService;
use OpenAI;

class AzureChatService extends OpenAIChatService
{
    public function __construct(
        // Azure OpenAI resource connection
        string $endpoint,
        string $apiKey,
        string $apiVersion,
        string $model,
    )
    {
        $client = OpenAI::factory()
            ->withBaseUri("$endpoint/openai/deployments/$model")
            ->withHttpHeader('api-key', $apiKey)
            ->withQueryParam('api-version', $apiVersion)
            ->make();

        parent::__construct($client->chat());
    }

    public static function make(): AzureChatService
    {
        return app(AzureChatService::class);
    }
}
