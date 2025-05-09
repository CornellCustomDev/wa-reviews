<?php

namespace LarAgent\History;

use Illuminate\Support\Facades\Storage;
use LarAgent\Core\Abstractions\ChatHistory;
use LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;

class FileChatHistory extends ChatHistory implements ChatHistoryInterface
{
    protected string $disk;   // The storage disk to use

    protected string $folder; // The folder path to store files

    protected string $keysFile = 'FileChatHistory-keys.json';

    public function __construct(string $name, array $options = [])
    {
        $this->disk = $options['disk'] ?? config('filesystems.default'); // Default to 'local' storage
        $this->folder = $options['folder'] ?? 'chat_histories'; // Default folder
        parent::__construct($name, $options);
    }

    public function readFromMemory(): void
    {
        $filePath = $this->getFullPath();

        if (Storage::disk($this->disk)->exists($filePath)) {
            $content = Storage::disk($this->disk)->get($filePath);

            try {
                $messages = json_decode($content, true);

                if (is_array($messages)) {
                    $this->setMessages($this->buildMessages($messages));
                } else {
                    $this->setMessages([]);
                }
            } catch (\Exception $e) {
                $this->setMessages([]);
            }
        } else {
            $this->setMessages([]);
        }
    }

    public function writeToMemory(): void
    {
        $filePath = $this->getFullPath();

        try {
            // Create directory if it doesn't exist
            $this->createFolderIfNotExists();

            // Write messages to the file
            Storage::disk($this->disk)->put($filePath, json_encode($this->toArrayForStorage(), JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to write chat history to file: {$filePath}");
        }
    }

    public function saveKeyToMemory(): void
    {
        try {
            $this->createFolderIfNotExists();
            $keysPath = $this->folder.'/'.$this->keysFile;
            $keys = $this->loadKeysFromMemory();

            $key = $this->getIdentifier();
            if (! in_array($key, $keys)) {
                $keys[] = $key;
                Storage::disk($this->disk)->put($keysPath, json_encode($keys, JSON_PRETTY_PRINT));
            }
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to save chat history key: {$this->getIdentifier()}");
        }
    }

    public function loadKeysFromMemory(): array
    {
        try {
            $keysPath = $this->folder.'/'.$this->keysFile;

            if (! Storage::disk($this->disk)->exists($keysPath)) {
                return [];
            }

            $content = Storage::disk($this->disk)->get($keysPath);

            return json_decode($content, true) ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function createFolderIfNotExists(): void
    {
        $directory = $this->folder;

        if (! Storage::disk($this->disk)->exists($directory)) {
            Storage::disk($this->disk)->makeDirectory($directory);
        }
    }

    protected function getSafeName(): string
    {
        $name = $this->getIdentifier();

        return preg_replace('/[^A-Za-z0-9_\-]/', '_', $name); // Sanitize the name
    }

    protected function getFullPath(): string
    {
        return $this->folder.'/'.$this->getSafeName().'.json';
    }

    public function removeChatFromMemory(string $key): void
    {
        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $key);
        $filePath = $this->folder.'/'.$safeName.'.json';

        if (Storage::disk($this->disk)->exists($filePath)) {
            Storage::disk($this->disk)->delete($filePath);
        }
        $this->removeChatKey($key);
    }

    protected function removeChatKey(string $key): void
    {
        $keys = $this->loadKeysFromMemory();
        $keys = array_filter($keys, fn ($k) => $k !== $key);

        $keysPath = $this->folder.'/'.$this->keysFile;
        Storage::disk($this->disk)->put($keysPath, json_encode($keys, JSON_PRETTY_PRINT));
    }
}
