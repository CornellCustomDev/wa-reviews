<?php

namespace App\AiAgents\Tools;

use App\Models\Guideline;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use LarAgent\Tool;

class FetchGuidelines extends Tool
{
    protected string $name = 'fetch_guidelines';

    protected string $description = 'Return the full text and metadata for up to five guidelines from the Guidelines Document.';

    public function getProperties(): array
    {
        return [
            'guideline_numbers' => [
                'type' => 'array',
                'description' => 'Array of guideline numbers (1‑5 items).',
                'items' => ['type' => 'integer'],
            ]
        ];
    }

    protected array $required = ['guideline_numbers'];

    public function execute(array $input): array
    {
        $numbers = $input['guideline_numbers'] ?? null;

        if (!is_array($numbers) || empty($numbers)) {
            return ['error' => 'numbers_parameter_missing'];
        }

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
