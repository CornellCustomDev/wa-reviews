<?php

namespace App\Services\GuidelinesAnalyzer\Tools;

use Illuminate\Support\Facades\Storage;

class FetchGuidelinesDocument extends Tool
{

    public function getName(): string
    {
        return 'fetch_guidelines_document';
    }

    public function getDescription(): string
    {
        return "Return the entire Guidelines Document (Cornell’s WCAG 2.2 AA testing guide) as markdown. Takes no arguments.";
    }

    public function call(?string $arguments = null): array
    {
        return [
            'markdown' => Storage::get('guidelines-list.md'),
        ];
    }
}
