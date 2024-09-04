<?php

namespace App\Services\AzureOpenAI;

use Illuminate\Support\Facades\Storage;
use OpenAI;
use OpenAI\Resources\Chat;
use OpenAI\Responses\Chat\CreateResponse;

class ChatService
{
    private Chat $chat;
    public ?array $params;
    public OpenAI\Client $client;

    public function __construct(
        string $endpoint,
        string $apiKey,
        string $searchKey,
        string $model,
        string $apiVersion,
        string $configFile,
    )
    {
        $this->client = OpenAI::factory()
            ->withBaseUri("$endpoint/openai/deployments/$model")
            ->withHttpHeader('api-key', $apiKey)
            ->withQueryParam('api-version', $apiVersion)
            ->make();

        $this->params = $this->getParameters(
            filename: $configFile,
            searchKey: $searchKey,
        );

        $this->chat = $this->client->chat();
    }

    public static function make(): ChatService
    {
        return app(ChatService::class);
    }

    public static function getParameters(
        string $filename,
        string $searchKey,
    ): ?array
    {
        $json = Storage::get($filename);
        if (empty($json)) {
            return null;
        }

        $config = json_decode($json, true);
        $config['azureSearchKey'] = $searchKey;

        return $config;
    }

    public function send(): CreateResponse
    {
        return $this->chat->create($this->params);
    }

    public function addMessage(string $message): array
    {
        $this->params['messages'][] = ['role' => 'user', 'content' => $message];

        return $this->params['messages'];
    }

}
