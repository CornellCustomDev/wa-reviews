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
                description: 'Array of guideline numbers (maximum of 5 items).',
                items: new NumberSchema('number', 'integer'),
            )
            ->using($this);
    }

    public function __invoke(array $guideline_numbers): string
    {
        // Validate there the guideline numbers are provided and not more than 5
        if (empty($guideline_numbers) || count($guideline_numbers) > 5) {
            return json_encode(['error' => 'You must provide an array of guideline numbers (maximum 5).']);
        }

        // Get all the guideline numbers in the database
        $existingGuidelines = Guideline::pluck('number')->toArray();

        // Validate that all guideline numbers are integers and in the existing guidelines
        $errors = [];
        foreach ($guideline_numbers as $number) {
            if (!is_int($number)) {
                $errors[] = "Guideline number '$number' is not an integer.";
            } elseif (!in_array($number, $existingGuidelines)) {
                $errors[] = "Guideline number '$number' does not exist.";
            }
        }
        if (!empty($errors)) {
            return json_encode(['error' => $errors]);
        }

        $guidelines = Guideline::query()
            ->whereIn('number', $guideline_numbers)
            ->with(['criterion', 'category'])
            ->get()
            ->map(fn($guideline) => GuidelinesAnalyzerService::mapGuidelineToSchema($guideline))
            ->toArray();

        return json_encode(['guidelines' => $guidelines], JSON_PRETTY_PRINT);
    }
}
