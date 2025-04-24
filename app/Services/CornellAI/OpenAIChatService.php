<?php

namespace App\Services\CornellAI;

use App\Services\GuidelinesAnalyzer\Tools\Tool;
use InvalidArgumentException;
use OpenAI\Exceptions\ErrorException;
use OpenAI\Exceptions\TransporterException;
use OpenAI\Exceptions\UnserializableResponse;
use OpenAI\Resources\Chat;

class OpenAIChatService
{
    protected array $parameters;
    protected array $messages = [];
    protected ?string $lastAiResponse;
    private array $tools = [];

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

    public function addUserMessage(string $content): void
    {
        $this->messages[] = ['role' => 'user', 'content' => $content];
    }

    public function setMessages(array $messages): void
    {
        $this->messages = $messages;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getChatMessages(): array
    {
        return collect($this->messages)
            ->filter(fn ($msg) => in_array($msg['role'], ['user', 'assistant']) && !empty($msg['content']))
            ->all();
    }

    public function setTools(array $tools): void
    {
        $this->tools = $tools;
        $this->parameters['tools'] = [];
        foreach ($tools as $tool) {
            if (!is_a($tool, Tool::class)) {
                throw new InvalidArgumentException("Tool must implement the Tool interface.");
            }

            $toolDefinition = [
                'type' => 'function',
                'function' => $tool->schema(),
            ];

            $this->parameters['tools'][] = $toolDefinition;
        }
    }

    public function requireJsonMode(): void
    {
        $this->parameters['response_format'] = ['type' => 'json_object'];
        $this->setResponseFormat('json_object');
    }

    public function setResponseFormat(string $type, ?array $schema = null): void
    {
        $this->parameters['response_format'] = ['type' => $type];
        if ($schema) {
            $this->parameters['response_format']['json_schema'] = $schema;
        }
    }

    /**
     * @throws ErrorException|UnserializableResponse|TransporterException
     */
    public function send(): array
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
        $messageCount = count($parameters['messages']);

        $response = $this->chat->create($parameters);

        // Add the messages to the chat history
        foreach ($response->choices as $result) {
            $message = $result->message;
            $this->messages[] = $message->toArray();
            if ($message->toolCalls) {
                foreach ($message->toolCalls as $toolCall) {
                    /** @var Tool $tool */
                    $tool = $this->tools[$toolCall->function->name] ?? null;
                    // TODO throw an error if we need to
                    if (!$tool) {
                        continue;
                    }
                    // TODO implement guardrails for tool calls
                    $toolResponse = $tool->call($toolCall->function->arguments);
                    if ($toolResponse) {
                        $this->messages[] = [
                            'role' => 'tool',
                            'content' => json_encode($toolResponse, JSON_PRETTY_PRINT) ?? '',
                            'tool_call_id' => $toolCall->id,
                        ];
                    }
                }
                // TODO Don't use recursion?
                return $this->send();
            }
        }

        return array_slice($this->messages, $messageCount);
    }

    public function getLastAiResponse(): string
    {
        $lastMessage = end($this->messages);

        return $lastMessage['content'];
    }

}
