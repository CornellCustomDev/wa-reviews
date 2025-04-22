<?php

namespace App\Services\GuidelinesAnalyzer\Tools;

abstract class Tool
{
    abstract public function getName(): string;
    abstract public function getDescription(): string;
    abstract public function call(string $arguments): array;
    abstract public function schema(): array;
}
