<?php

namespace App\AiAgents\Tools;

use App\Models\Guideline;

class FetchGuidelinesListTool extends BaseTool
{
    protected string $description =
        'Return a list of all accessibility guidelines with number, name, WCAG 2.2 AA criterion, and category.';

    public function handle(array $input): array
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
