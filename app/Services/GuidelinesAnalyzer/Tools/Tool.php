<?php

namespace App\Services\GuidelinesAnalyzer\Tools;

abstract class Tool
{
    abstract public function getName(): string;
    abstract public function getDescription(): string;
    abstract public function call(string $arguments): array;
    public function schema(): array
    {
        // Default schema for tools requiring no arguments
        return [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'parameters' => [
                'type' => 'object',
                'properties' => new \stdClass(),
                'additionalProperties' => false,
            ],
            'strict' => true,
        ];
    }
}
