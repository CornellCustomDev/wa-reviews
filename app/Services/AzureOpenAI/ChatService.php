<?php

namespace App\Services\AzureOpenAI;

use OpenAI;
use OpenAI\Exceptions\ErrorException;
use OpenAI\Exceptions\TransporterException;
use OpenAI\Exceptions\UnserializableResponse;
use OpenAI\Resources\Chat;

class ChatService
{
    protected Chat $chat;
    protected array $messages = [];
    private ?string $lastAiResponse;

    public function __construct(
        // Azure OpenAI resource connection
        string $endpoint,
        string $apiKey,
        string $apiVersion,
        string $model,
        // Chat parameters
        protected string $prompt = 'You are an AI chatbot. You are here to help users with web accessibility issues.',
        protected float $temperature = 0.0, // Try setting to 0.0 for deterministic responses
        protected float $top_p = 0.95,
        protected int $max_tokens = 800,
    )
    {
        $client = OpenAI::factory()
            ->withBaseUri("$endpoint/openai/deployments/$model")
            ->withHttpHeader('api-key', $apiKey)
            ->withQueryParam('api-version', $apiVersion)
            ->make();

        $this->chat = $client->chat();
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
        $parameters = [
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->prompt,
                ],
                ...$this->messages,
            ],
            'temperature' => $this->temperature,
            'top_p' => $this->top_p,
            'max_tokens' => $this->max_tokens,
        ];

        $response = $this->chat->create($parameters);

        // Add the messages to the chat history
        foreach ($response->choices as $result) {
            $this->addMessage(message: $result->message->content, role: $result->message->role);
        }
    }

    public function getLastAiResponse(): string
    {
        $lastMessage = end($this->messages);

        return $lastMessage['content'];
    }

    public function getPrompt(): string
    {
        return $this->prompt;
    }

    public function setPrompt(string $prompt): void
    {
        $this->prompt = $prompt;
    }

    public function addMessage(string $message, string $role = 'user'): array
    {
        $this->messages[] = ['role' => $role, 'content' => $message];

        return $this->messages;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function setMessages(array $messages): void
    {
        $this->messages = $messages;
    }

}
