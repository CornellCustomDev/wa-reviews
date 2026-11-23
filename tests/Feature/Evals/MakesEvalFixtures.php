<?php

namespace Tests\Feature\Evals;

use App\Models\Guideline;
use App\Models\Page;
use App\Models\Scope;
use Illuminate\Support\Facades\Storage;

trait MakesEvalFixtures
{
    private int $guidelineSequence = 0;

    /**
     * A scope with stored page HTML on a faked disk. The recommender is only
     * replayable when that content is actually retrievable.
     */
    protected function makeScopeWithPageContent(string $html = '<html><body><img src="a.png"></body></html>'): Scope
    {
        Storage::fake();

        $scope = Scope::factory()->create();

        // `latestPage` matches on the scope's own url, so the page must share it.
        $scope->setCurrentPage(Page::createPageContent($scope, $scope->url, $html));

        return $scope->refresh();
    }

    /**
     * The `guidelines` table has no auto-increment — ids mirror the published
     * guideline numbers — so test guidelines need an explicit key well clear of
     * the seeded range.
     */
    protected function makeGuideline(): Guideline
    {
        $number = 9001 + $this->guidelineSequence++;

        $attributes = Guideline::factory()->make(['number' => $number])->getAttributes();

        Guideline::query()->insert($attributes + [
            'id' => $number,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Guideline::findOrFail($number);
    }
}
