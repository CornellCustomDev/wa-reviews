<?php

namespace App\Ai\Prism\Tools;

use App\Models\Guideline;
use Prism\Prism\Tool;

class FetchGuidelinesListTool extends Tool
{
    public function __construct()
    {
        $this->as('fetch_guidelines_list')
            ->for('Return a list of all accessibility guidelines with number, name, WCAG 2.2 AA criterion, and category.')
            ->using($this);
    }

    public function __invoke(): string
    {
        $guidelinesList = Guideline::query()
            ->with(['criterion', 'category'])
            ->get()
            ->map(function ($guideline) {
                return [
                    'number' => $guideline->number,
                    'name' => $guideline->name,
                    'wcag_criterion' => $guideline->criterion->getNumberName(),
                    'category' => $guideline->category->name,
                    'url' => config('app.url') . '/guidelines/' . $guideline->id,
                ];
            });

        return json_encode(['guidelines' => $guidelinesList], JSON_PRETTY_PRINT);
    }
}
