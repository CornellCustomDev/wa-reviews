<?php

namespace LarAgent\Messages;

use LarAgent\Core\Contracts\Message as MessageInterface;

class ToolCallMessage extends AssistantMessage implements MessageInterface
{
    protected array $toolCalls = [];

    protected mixed $toMessageCallback;

    public function __construct(array $toolCalls, array $message, array $metadata = [])
    {
        parent::__construct('', $metadata);
        $this->buildFromArray($message);
        $this->toolCalls = $toolCalls;
    }

    public function getToolCalls(): array
    {
        return $this->toolCalls;
    }
}
