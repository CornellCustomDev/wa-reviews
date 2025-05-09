<?php

namespace LarAgent\Core\Contracts;

use LarAgent\Core\Contracts\Message as MessageInterface;

interface ChatHistory
{
    public function addMessage(MessageInterface $message): void;

    public function getMessages(): array;

    public function getIdentifier(): string;

    public function getLastMessage(): ?MessageInterface;

    public function clear(): void;

    public function count(): int;

    public function toArray(): array;

    public function readFromMemory(): void;

    public function writeToMemory(): void;

    public function setContextWindow(int $tokens): void;

    public function exceedsContextWindow(int $tokens): bool;

    public function truncateOldMessages(int $messagesCount): void;
}
