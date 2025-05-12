<?php

namespace LarAgent\History;

use Illuminate\Support\Facades\Session;
use LarAgent\Core\Abstractions\ChatHistory;
use LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;

class SessionChatHistory extends ChatHistory implements ChatHistoryInterface
{
    protected string $keysKey = 'SessionChatHistory-keys';

    public function readFromMemory(): void
    {
        $messages = Session::get($this->getIdentifier(), []);
        if (! is_array($messages)) {
            $messages = [];
        }
        $this->setMessages($messages);
    }

    public function writeToMemory(): void
    {
        $messages = $this->getMessages();
        Session::put($this->getIdentifier(), $messages);
    }

    public function saveKeyToMemory(): void
    {
        $keys = $this->loadKeysFromMemory();
        $key = $this->getIdentifier();
        if (! in_array($key, $keys)) {
            $keys[] = $key;
            Session::put($this->keysKey, $keys);
        }
    }

    public function loadKeysFromMemory(): array
    {
        $keys = Session::get($this->keysKey, []);

        return is_array($keys) ? $keys : [];
    }

    public function removeChatFromMemory(string $key): void
    {
        Session::forget($key);
        $this->removeChatKey($key);
    }

    protected function removeChatKey(string $key): void
    {
        $keys = $this->loadKeysFromMemory();
        $keys = array_filter($keys, fn ($k) => $k !== $key);
        Session::put($this->keysKey, $keys);
    }
}
