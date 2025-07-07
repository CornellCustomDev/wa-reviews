<?php

namespace App\Ai\Prism\Tools;

use Illuminate\Support\Facades\Storage;
use Prism\Prism\Schema\EnumSchema;
use Prism\Prism\Tool;

class ScratchPadTool extends Tool
{
    public function __construct()
    {
        $this->as('scratch_pad')
            ->for('A scratch pad for the agent to document planning and reflection.')
            ->withEnumParameter(
                name: 'action',
                description: 'The action to perform on the scratch pad.',
                options: ['store', 'append'],
            )
            ->withStringParameter(
                name: 'data',
                description: 'The text to be stored on the scratch pad.',
            )
            ->using($this);
    }

    public function __invoke(string $action, string $data): string
    {
        // Add a timestamp to the data for better tracking
        $data = "#" . date('Y-m-d H:i:s') . "\n" . $data;

        return match ($action) {
            //'store' => $this->storeScratchPad($data),
            'store' => $this->appendScratchPad($data),
            'append' => $this->appendScratchPad($data),
//            'retrieve' => $this->retrieveScratchPad(),
//            'clear' => $this->clearScratchPad(),
            default => json_encode(['error' => 'invalid_action']),
        };
    }


    private function storeScratchPad(string $data): string
    {
        // For initial POC, store in a file using Laravel's Storage
        Storage::put('scratch_pad.txt', $data);

        return json_encode(['status' => 'success', 'message' => 'Scratch pad updated.']);
    }

    private function appendScratchPad(string $data): string
    {
        // For initial POC, append to a file using Laravel's Storage
        Storage::append('scratch_pad.txt', "\n---\n" . $data);

        return json_encode(['status' => 'success', 'message' => 'Data appended to scratch pad.']);
    }

    private function retrieveScratchPad(): string
    {
        // For initial POC, read from a file using Laravel's Storage
        $data = Storage::get('scratch_pad.txt');

        return json_encode(['status' => 'success', 'data' => $data]);
    }

    private function clearScratchPad(): string
    {
        // For initial POC, delete the file using Laravel's Storage
        Storage::delete('scratch_pad.txt');

        return json_encode(['status' => 'success', 'message' => 'Scratch pad cleared.']);
    }
}
