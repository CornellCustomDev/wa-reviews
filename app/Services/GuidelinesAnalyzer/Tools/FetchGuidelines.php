<?php

namespace App\Services\GuidelinesAnalyzer\Tools;

use App\Models\Guideline;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerServiceInterface;

class FetchGuidelines extends Tool
{
    public function __construct(
        private readonly GuidelinesAnalyzerServiceInterface $guidelinesAnalyzerService,
    ) {}

    public function getName(): string
    {
        return 'fetch_guidelines';
    }

    public function getDescription(): string
    {
        return 'Return the full text and metadata for up to five guidelines from the Guidelines Document.';
    }

    public function call(string $arguments): array
    {
        $arguments = json_decode($arguments, true);
        $numbers = $arguments['guideline_numbers'] ?? null;

        if (!is_array($numbers) || empty($numbers)) {
            return ['error' => 'numbers_parameter_missing'];
        }

        if (count($numbers) > 5) {
            return ['error' => 'too_many_numbers_requested'];
        }

        $guidelines = Guideline::whereIn('number', $numbers)
            ->with(['criterion', 'category'])
            ->get()
            ->map(fn ($guideline) => $this->guidelinesAnalyzerService->mapGuidelineToSchema($guideline))
            ->toArray();

        return ['guidelines' => $guidelines];
    }

    public function schema(): array
    {
        return [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'guideline_numbers' => [
                        'type' => 'array',
                        'description' => 'Array of guideline numbers (1‑5 items).',
                        'items' => ['type' => 'integer'],
                    ]
                ],
                'required' => ['guideline_numbers'],
                'additionalProperties' => false,
            ],
            'strict' => true,
        ];
    }
}
