<?php

namespace App\AiAgents\Tools;

use App\Models\Guideline;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;

class FetchGuidelinesTool extends BaseTool
{
    protected string $description = 'Return the full text and metadata for up to five guidelines from the Guidelines Document.';

    protected static array $schema = [
        'guideline_numbers' => [
            'type' => 'array',
            'description' => 'Array of guideline numbers (1‑5 items).',
            'items' => ['type' => 'integer'],
            'minItems'    => 1,
            'maxItems'    => 5,
        ]
    ];

    public function handle(array $input): array
    {
        $numbers = $input['guideline_numbers'] ?? null;

        if (count($numbers) > 5) {
            return ['error' => 'too_many_numbers_requested'];
        }

        $guidelines = Guideline::whereIn('number', $numbers)
            ->with(['criterion', 'category'])
            ->get()
            ->map(fn ($guideline) => GuidelinesAnalyzerService::mapGuidelineToSchema($guideline))
            ->toArray();

        return ['guidelines' => $guidelines];
    }
}
