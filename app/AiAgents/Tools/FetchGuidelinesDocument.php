<?php

namespace App\AiAgents\Tools;

use Illuminate\Support\Facades\Storage;
use LarAgent\Tool;

class FetchGuidelinesDocument extends Tool
{
    protected string $name = 'fetch_guidelines_document';

    protected string $description = 'Return the entire list of guidelines from the Guidelines Document (Cornell’s WCAG 2.2 AA testing guide) as markdown.';

    public function execute(array $input): array
    {
        return [
            'markdown' => Storage::get('guidelines-list.md'),
        ];
    }
}
