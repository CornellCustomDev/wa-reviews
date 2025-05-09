<?php

namespace LarAgent\Core\Contracts;

interface ToolCall
{
    public function getId(): string;

    public function getToolName(): string;

    public function getArguments(): string;
}
