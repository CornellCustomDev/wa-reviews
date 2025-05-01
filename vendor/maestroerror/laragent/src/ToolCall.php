<?php

namespace LarAgent;

use LarAgent\Core\Contracts\ToolCall as ToolCallInterface;

class ToolCall implements ToolCallInterface
{
    protected string $id;

    protected string $toolName;

    protected string $arguments;

    public function __construct(string $id, string $toolName, string $arguments)
    {
        $this->id = $id;
        $this->toolName = $toolName;
        $this->arguments = $arguments;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getToolName(): string
    {
        return $this->toolName;
    }

    public function getArguments(): string
    {
        return $this->arguments;
    }
}
