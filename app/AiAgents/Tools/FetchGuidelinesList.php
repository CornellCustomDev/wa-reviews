<?php

namespace App\AiAgents\Tools;

use App\Models\Guideline;
use LarAgent\Tool;

class FetchGuidelinesList extends Tool
{
    protected string $name = 'fetch_guidelines_list';

    protected string $description = 'Return a list of all accessibility guidelines with number, name, WCAG 2.2 AA criterion, and category.';

    public function execute(array $input): array
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
