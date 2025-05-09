<?php

namespace App\Services\GuidelinesAnalyzer\Tools;

use App\Models\Guideline;

class FetchGuidelinesList extends Tool
{
    public function getName(): string
    {
        return 'fetch_guidelines_list';
    }

    public function getDescription(): string
    {
        return 'Return a list of all accessibility guidelines with number, name, WCAG 2.2 AA criterion, and category.';
    }

    public function call(?string $arguments = null): array
    {
        $guidelinesList = Guideline::query()->with(['criterion', 'category'])
            ->get()
            ->map(function ($guideline) {
                return [
                    'number'        => $guideline->number,
                    'name'          => $guideline->name,
                    'wcag_criterion'=> $guideline->criterion->getNumberName(),
                    'category'      => $guideline->category->name,
                    'url'           => config('app.url') . '/guidelines/' . $guideline->id,
                ];
            });

        return ['guidelines' => $guidelinesList];
    }
}
