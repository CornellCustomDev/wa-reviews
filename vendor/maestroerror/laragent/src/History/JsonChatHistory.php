<?php

namespace LarAgent\History;

use LarAgent\Core\Abstractions\ChatHistory;
use LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;

class JsonChatHistory extends ChatHistory implements ChatHistoryInterface
{
    protected string $folder = '';

    protected string $keysFile = 'JsonChatHistory-keys.json';

    public function __construct(string $name, array $options = [])
    {
        // By default store the JSON files in the json_storage folder at project root
        $this->folder = $options['folder'] ?? dirname(__DIR__, 2).'/json_storage';
        parent::__construct($name, $options);
    }

    public function readFromMemory(): void
    {
        // Check the folder exists
        $this->createFolderIfNotExists();
        // Get full file location
        $file = $this->getFullPath();
        if (file_exists($file) === false) {
            $this->setMessages([]);

            return;
        }
        // Read JSON
        $content = file_get_contents($file);
        // Build messages
        $this->setMessages($this->buildMessages(json_decode($content, true)));

    }

    public function writeToMemory(): void
    {
        $this->createFolderIfNotExists();
        // Get full file location
        $file = $this->getFullPath();
        // Create json file
        file_put_contents($file, json_encode($this->toArrayForStorage()));
    }

    public function saveKeyToMemory(): void
    {
        $this->createFolderIfNotExists();
        $keysPath = $this->folder.'/'.$this->keysFile;
        $keys = $this->loadKeysFromMemory();

        $key = $this->getIdentifier();
        if (! in_array($key, $keys)) {
            $keys[] = $key;
            file_put_contents($keysPath, json_encode($keys));
        }
    }

    public function loadKeysFromMemory(): array
    {
        $keysPath = $this->folder.'/'.$this->keysFile;

        if (! file_exists($keysPath)) {
            return [];
        }

        $content = file_get_contents($keysPath);

        return json_decode($content, true) ?? [];
    }

    protected function createFolderIfNotExists(): void
    {
        if (! file_exists($this->folder)) {
            mkdir($this->folder, 0777, true);
        }
    }

    protected function getSafeName(): string
    {
        $name = $this->getIdentifier();

        return preg_replace('/[^A-Za-z0-9_\-]/', '_', $name); // Replace unsafe characters with underscores
    }

    protected function getFullPath(): string
    {
        return $this->folder.'/'.$this->getSafeName().'.json';
    }

    public function removeChatFromMemory(string $key): void
    {
        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $key);
        $filePath = $this->folder.'/'.$safeName.'.json';

        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $this->removeChatKey($key);
    }

    protected function removeChatKey(string $key): void
    {
        $keys = $this->loadKeysFromMemory();
        $keys = array_filter($keys, fn ($k) => $k !== $key);

        $keysPath = $this->folder.'/'.$this->keysFile;
        file_put_contents($keysPath, json_encode($keys));
    }
}
