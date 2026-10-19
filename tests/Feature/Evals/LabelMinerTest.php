<?php

namespace Tests\Feature\Evals;

use App\Enums\AIStatus;
use App\Models\ChatHistory;
use App\Models\Issue;
use App\Models\Item;
use App\Models\Scope;
use App\Services\Evals\LabelMiner;
use Tests\Feature\FeatureTestCase;

class LabelMinerTest extends FeatureTestCase
{
    use MakesEvalFixtures;

    public function test_it_mines_reviewer_verdicts_from_issues(): void
    {
        $scope = Scope::factory()->create();
        $history = $this->historyForScope($scope);

        $accepted = $this->issue($scope, $history, AIStatus::Accepted);
        $modified = $this->issue($scope, $history, AIStatus::Modified);
        $rejected = $this->issue($scope, $history, AIStatus::Rejected);
        $untriaged = $this->issue($scope, $history, AIStatus::Generated);

        $labels = (new LabelMiner)->mine($history);

        $this->assertSame('issues', $labels['source']);
        $this->assertSame([$accepted->guideline_id], $labels['accepted_guideline_ids']);
        $this->assertSame([$modified->guideline_id], $labels['modified_guideline_ids']);
        $this->assertSame([$rejected->guideline_id], $labels['rejected_guideline_ids']);
        $this->assertSame([$untriaged->guideline_id], $labels['untriaged_guideline_ids']);
        $this->assertCount(4, $labels['produced_guideline_ids']);
        $this->assertSame(3, $labels['triaged_count'], 'Untriaged rows must stay out of the denominator.');
    }

    public function test_it_treats_later_human_authored_issues_as_misses(): void
    {
        $scope = Scope::factory()->create();
        $history = $this->historyForScope($scope);
        $this->issue($scope, $history, AIStatus::Accepted);

        $humanAuthored = Issue::factory()->create([
            'scope_id' => $scope->id,
            'project_id' => $scope->project_id,
            'guideline_id' => $this->makeGuideline()->id,
            'ai_status' => null,
            'created_at' => $history->created_at->addDay(),
        ]);

        $labels = (new LabelMiner)->mine($history);

        $this->assertSame([$humanAuthored->guideline_id], $labels['missed_guideline_ids']);
    }

    public function test_it_records_pre_existing_guidelines_so_they_do_not_count_against_precision(): void
    {
        $scope = Scope::factory()->create();
        $history = $this->historyForScope($scope);
        $this->issue($scope, $history, AIStatus::Accepted);

        $preExisting = Issue::factory()->create([
            'scope_id' => $scope->id,
            'project_id' => $scope->project_id,
            'guideline_id' => $this->makeGuideline()->id,
            'ai_status' => null,
            'created_at' => $history->created_at->subDay(),
        ]);

        $labels = (new LabelMiner)->mine($history);

        $this->assertSame([$preExisting->guideline_id], $labels['pre_existing_guideline_ids']);
        $this->assertSame([], $labels['missed_guideline_ids']);
    }

    public function test_it_mines_items_for_an_issue_context(): void
    {
        $issue = Issue::factory()->create();
        $history = ChatHistory::factory()->recommender()->create([
            'context_type' => Issue::class,
            'context_id' => $issue->id,
        ]);

        $item = Item::factory()->create([
            'issue_id' => $issue->id,
            'guideline_id' => $this->makeGuideline()->id,
            'chat_history_ulid' => $history->ulid,
            'ai_status' => AIStatus::Accepted,
        ]);

        $labels = (new LabelMiner)->mine($history);

        $this->assertSame('items', $labels['source']);
        $this->assertSame([$item->guideline_id], $labels['accepted_guideline_ids']);
    }

    public function test_it_returns_null_when_the_run_produced_nothing(): void
    {
        $history = $this->historyForScope(Scope::factory()->create());

        $this->assertNull((new LabelMiner)->mine($history));
    }

    private function historyForScope(Scope $scope): ChatHistory
    {
        return ChatHistory::factory()->recommender()->create([
            'context_type' => Scope::class,
            'context_id' => $scope->id,
        ]);
    }

    private function issue(Scope $scope, ChatHistory $history, AIStatus $status): Issue
    {
        return Issue::factory()->create([
            'scope_id' => $scope->id,
            'project_id' => $scope->project_id,
            'guideline_id' => $this->makeGuideline()->id,
            'chat_history_ulid' => $history->ulid,
            'ai_status' => $status,
            'created_at' => $history->created_at,
        ]);
    }
}
