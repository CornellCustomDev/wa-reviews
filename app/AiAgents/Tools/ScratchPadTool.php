<?php

namespace App\AiAgents\Tools;

use Illuminate\Support\Facades\Storage;
use LarAgent\Tool;

class ScratchPadTool extends Tool
{
    protected string $name = 'scratch_pad';
    protected string $description = 'A scratch pad for the agent to use.';
    protected array $required = ['action', 'data'];

    public static function call(string $action, string $data): mixed
    {
        return (new self())->execute([
            'action' => $action,
            'data' => $data,
        ]);
    }

    public function getProperties(): array
    {
        return [
            'action' => [
                'type' => 'string',
                'description' => 'The action to perform on the scratch pad: "store" or "append".',
            ],
            'data' => [
                'type' => ['string'],
                'description' => 'The text to be stored on the scratch pad. Required for "store", and "append" actions.',
            ],
        ];
    }

    public function execute(array $input): mixed
    {
        $action = $input['action'] ?? null;
        $data = $input['data'] ?? null;

        return match ($action) {
            'store' => $this->storeScratchPad($data),
            'append' => $this->appendScratchPad($data),
//            'retrieve' => $this->retrieveScratchPad(),
//            'clear' => $this->clearScratchPad(),
            default => ['error' => 'invalid_action'],
        };
    }

    private function storeScratchPad(string $data): array
    {
        // Update the scratch pad with the provided data

        // For initial POC, store in a file using Laravel's Storage
        Storage::put('scratch_pad.txt', $data);

        return ['status' => 'success', 'message' => 'Scratch pad updated.'];
    }

    private function appendScratchPad(string $data): array
    {
        // Append data to the scratch pad

        // For initial POC, append to a file using Laravel's Storage
        Storage::append('scratch_pad.txt', "\n---\n" . $data);

        return ['status' => 'success', 'message' => 'Data appended to scratch pad.'];
    }

    private function retrieveScratchPad(): array
    {
        // Retrieve the contents of the scratch pad

        // For initial POC, read from a file using Laravel's Storage
        $data = Storage::get('scratch_pad.txt');

        return ['status' => 'success', 'data' => $data];
    }

    private function clearScratchPad(): array
    {
        // Clear the scratch pad

        // For initial POC, delete the file using Laravel's Storage
        Storage::delete('scratch_pad.txt');

        return ['status' => 'success', 'message' => 'Scratch pad cleared.'];
    }
}
