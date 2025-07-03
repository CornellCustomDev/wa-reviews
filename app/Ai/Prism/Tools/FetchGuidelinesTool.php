<?php

namespace App\Ai\Prism\Tools;

use App\Models\Guideline;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Tool;

class FetchGuidelinesTool extends Tool
{
    public function __construct()
    {
        $this->as('fetch_guidelines')
            ->for('Fetch guidelines by their numbers.')
            ->withArrayParameter(
                name: 'guideline_numbers',
                description: 'Array of guideline numbers (1‑5 items).',
                items: new NumberSchema('number', 'integer'),
            )
            ->using($this);
    }

    public function __invoke(array $guideline_numbers): string
    {
        $guidelines = Guideline::query()
            ->whereIn('number', $guideline_numbers)
            ->with(['criterion', 'category'])
            ->get()
            ->map(fn($guideline) => GuidelinesAnalyzerService::mapGuidelineToSchema($guideline))
            ->toArray();

        return json_encode(['guidelines' => $guidelines], JSON_PRETTY_PRINT);
    }
}
