<?php

namespace LarAgent\Core\Contracts;

interface Message
{
    public function getRole(): string;

    public function getContent(): string|array;

    public function setContent(string|array $message): void;

    public function get(string $key): mixed;

    public function getMetadata(): array;

    public function setMetadata(array $data): void;

    public function toArray(): array;

    public function toArrayWithMeta(): array;

    public function jsonSerialize(): array;
}
