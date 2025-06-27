<?php

namespace App\AiAgents\Tools;

use Illuminate\Support\Facades\Storage;

class FetchGuidelinesDocumentTool extends BaseTool
{
    protected string $description =
        'Return the entire list of guidelines from the Guidelines Document (Cornell’s WCAG 2.2 AA testing guide) as markdown.';

    public function handle(array $input): array
    {
        return [
            'markdown' => Storage::get('guidelines-list.md'),
        ];
    }
}
