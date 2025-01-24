<?php

namespace App\Services\CornellAI;

use OpenAI\Exceptions\ErrorException;
use OpenAI\Exceptions\TransporterException;
use OpenAI\Exceptions\UnserializableResponse;
use OpenAI\Resources\Chat;

abstract class OpenAIChatService
{
    protected array $parameters;
    protected array $messages = [];
    protected ?string $lastAiResponse;

    public function __construct(
        protected Chat   $chat,
        protected string $prompt = 'You are an AI chatbot. You are here to help users with web accessibility issues.',
        ?string          $model = null,
        float            $temperature = 0.1, // Try setting to 0.0 for deterministic responses
        float            $top_p = 0.95,
        int              $max_tokens = 800,
    )
    {
        $this->parameters = [
            'temperature' => $temperature,
            'top_p' => $top_p,
            'max_tokens' => $max_tokens,
        ];

        if ($model) {
            $this->parameters['model'] = $model;
        }
    }

    public function setPrompt(string $prompt): void
    {
        $this->prompt = $prompt;
    }

    public function getPrompt(): string
    {
        return $this->prompt;
    }

    public function addMessage(string $message, string $role = 'user'): array
    {
        $this->messages[] = ['role' => $role, 'content' => $message];

        return $this->messages;
    }

    public function setMessages(array $messages): void
    {
        $this->messages = $messages;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @throws ErrorException|UnserializableResponse|TransporterException
     */
    public function send(): void
    {
        $parameters = [
            ...$this->parameters,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->prompt,
                ],
                ...$this->messages,
            ],
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
}
