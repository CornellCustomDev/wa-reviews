<?php

namespace App\Services\AzureOpenAI;

use Illuminate\Support\Facades\Storage;
use OpenAI;
use OpenAI\Exceptions\ErrorException;
use OpenAI\Exceptions\TransporterException;
use OpenAI\Exceptions\UnserializableResponse;
use OpenAI\Resources\Chat;
use OpenAI\Responses\Chat\CreateResponse;

class ChatService
{
    protected OpenAI\Client $client;
    protected Chat $chat;
    protected ?array $params;
    protected CreateResponse $response;

    public function __construct(
        // Azure OpenAI resource connection
        string $endpoint,
        string $apiKey,
        string $apiVersion,
        string $model,
        // Search resource connection
        string $searchEndpoint,
        string $searchKey,
        string $indexName,
        // Chat configuration
        string $roleInformation,
    )
    {
        $this->client = OpenAI::factory()
            ->withBaseUri("$endpoint/openai/deployments/$model")
            ->withHttpHeader('api-key', $apiKey)
            ->withQueryParam('api-version', $apiVersion)
            ->make();

        $config = new ChatConfig(
            search_endpoint: $searchEndpoint,
            search_key: $searchKey,
            index_name: $indexName,
            role_information: $roleInformation,
            top_p: 0.1,
        );
        $this->params = $config->getParameters();

        $this->chat = $this->client->chat();
    }

    public static function make(): ChatService
    {
        return app(ChatService::class);
    }

    /**
     * @throws ErrorException|UnserializableResponse|TransporterException
     */
    public function send(): void
    {
        $this->response = $this->chat->create($this->params);
        // Add the messages to the chat history
        foreach ($this->response->choices as $result) {
            $this->params['messages'][] = ['role' => $result->message->role, 'content' => $result->message->content];
        }
    }

    public function addMessage(string $message): array
    {
        $this->params['messages'][] = ['role' => 'user', 'content' => $message];

        return $this->params['messages'];
    }

    public function getMessages(): array
    {
        return $this->params['messages'];
    }

    public function setMessages(array $messages): void
    {
        $this->params['messages'] = $messages;
    }

}
