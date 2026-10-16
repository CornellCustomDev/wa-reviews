<?php

namespace Tests\Feature\Evals;

use App\Enums\Agents;
use App\Enums\AIStatus;
use App\Models\Agent;
use App\Models\ChatHistory;
use App\Models\EvalCase;
use App\Models\Issue;
use App\Models\Page;
use App\Models\Scope;
use App\Services\Evals\CaseExtractor;
use Tests\Feature\FeatureTestCase;

class CaseExtractorTest extends FeatureTestCase
{
    use MakesEvalFixtures;

    public function test_it_extracts_a_recommender_case_with_mined_labels(): void
    {
        $scope = $this->makeScopeWithPageContent();
        $history = ChatHistory::factory()->recommender([11, 12])->create([
            'context_type' => Scope::class,
            'context_id' => $scope->id,
        ]);

        $accepted = Issue::factory()->create([
            'scope_id' => $scope->id,
            'project_id' => $scope->project_id,
            'guideline_id' => $this->makeGuideline()->id,
            'chat_history_ulid' => $history->ulid,
            'ai_status' => AIStatus::Accepted,
        ]);

        $attributes = $this->extractor()->extract($history, 'v1');

        $this->assertSame($scope->id, $attributes['input']['scope_id']);
        $this->assertStringContainsString('Web page scope', $attributes['input']['prompt']);
        $this->assertSame([11, 12], $attributes['baseline']['guideline_ids']);
        $this->assertSame('Stop', $attributes['baseline']['finish_reason']);
        $this->assertSame(104750, $attributes['baseline']['usage']['promptTokens']);
        $this->assertSame([$accepted->guideline_id], $attributes['labels']['mined']['accepted_guideline_ids']);
        $this->assertSame(Agents::GuidelineRecommender->value, $attributes['tags']['agent']);
        $this->assertSame('Scope', $attributes['tags']['context']);
        $this->assertSame('guidelines', $attributes['tags']['outcome']);
        $this->assertSame('scratch_pad', $attributes['tags']['tool_pattern']);
        $this->assertTrue($attributes['tags']['has_labels']);
    }

    public function test_it_resolves_the_scope_through_the_issue_for_an_issue_context(): void
    {
        $scope = $this->makeScopeWithPageContent();
        $issue = Issue::factory()->create([
            'scope_id' => $scope->id,
            'project_id' => $scope->project_id,
        ]);
        $history = ChatHistory::factory()->recommender()->create([
            'context_type' => Issue::class,
            'context_id' => $issue->id,
        ]);

        $attributes = $this->extractor()->extract($history, 'v1');

        $this->assertSame($issue->scope_id, $attributes['input']['scope_id']);
        $this->assertSame(Issue::class, $attributes['context_type']);
        $this->assertSame('Issue', $attributes['tags']['context']);
        $this->assertFalse($attributes['tags']['has_labels']);
        $this->assertNull($attributes['labels']);
    }

    public function test_it_skips_a_recommender_case_whose_scope_is_gone(): void
    {
        $history = ChatHistory::factory()->recommender()->create([
            'context_type' => Scope::class,
            'context_id' => 999999,
        ]);

        $this->assertNull($this->extractor()->extract($history, 'v1'));
    }

    public function test_it_skips_a_recommender_case_whose_stored_page_content_is_missing(): void
    {
        $history = ChatHistory::factory()->recommender()->create([
            'context_type' => Scope::class,
            'context_id' => Scope::factory()->create()->id,
        ]);

        $this->assertNull(
            $this->extractor()->extract($history, 'v1'),
            'Replaying against empty page content would grade both models on nothing.'
        );
    }

    public function test_it_skips_a_recommender_case_whose_page_is_larger_than_production_will_analyse(): void
    {
        config()->set('cornell_ai.page_content_char_limit', 10);
        $history = ChatHistory::factory()->recommender()->create([
            'context_type' => Scope::class,
            'context_id' => $this->makeScopeWithPageContent('<html><body>This page is longer than ten characters.</body></html>')->id,
        ]);
        $extractor = $this->extractor();

        $this->assertNull($extractor->extract($history, 'v1'));
        $this->assertArrayHasKey('page content exceeds the analysis limit', $extractor->drainSkipReasons());
    }

    public function test_it_unwraps_the_data_payload_for_a_structured_output_case(): void
    {
        $history = ChatHistory::factory()->structuredOutput('Recommendation prose.')->create();

        $attributes = $this->extractor()->extract($history, 'v1');

        $this->assertSame('Recommendation prose.', $attributes['input']['data']);
        $this->assertSame('recommended_guidelines', $attributes['input']['schema']);
        $this->assertSame([4], $attributes['baseline']['guideline_ids']);
        $this->assertNull($attributes['labels'], 'StructuredOutput cases are self-contained.');
    }

    public function test_it_derives_a_structured_output_case_from_a_recommender_transcript(): void
    {
        $history = ChatHistory::factory()->recommender([21, 22])->create();

        $attributes = $this->extractor()->extract($history, 'v1', Agents::StructuredOutput);

        $this->assertSame(
            Agent::findAgent(Agents::StructuredOutput)->id,
            $attributes['agent_id'],
            'The case belongs to the agent being evaluated, not the one that produced the transcript.'
        );
        $this->assertSame('recommended_guidelines', $attributes['input']['schema']);
        $this->assertStringContainsString('"number":21', $attributes['input']['data']);

        // The recommender text is the input, so its guideline ids are what the
        // transformation must preserve — there is no historical answer to keep.
        $this->assertSame([21, 22], $attributes['baseline']['guideline_ids']);
        $this->assertSame(Agents::GuidelineRecommender->value, $attributes['baseline']['derived_from']);
        $this->assertNull($attributes['baseline']['usage']);
        $this->assertNull($attributes['baseline']['finish_reason']);
        $this->assertSame([], $attributes['baseline']['tool_calls']);

        $this->assertTrue($attributes['tags']['derived']);
        $this->assertSame('n/a', $attributes['tags']['page_size']);
        $this->assertNull($attributes['labels'], 'Mined verdicts describe the recommender, not the transformation.');
    }

    public function test_a_derived_case_coexists_with_the_recommender_case_from_the_same_transcript(): void
    {
        $history = ChatHistory::factory()->recommender()->create([
            'context_type' => Scope::class,
            'context_id' => $this->makeScopeWithPageContent()->id,
        ]);
        $extractor = $this->extractor();

        $extractor->store($extractor->extract($history, 'v1'));
        $extractor->store($extractor->extract($history, 'v1', Agents::StructuredOutput));

        $this->assertSame(2, EvalCase::where('source_chat_history_ulid', $history->ulid)->count());
    }

    public function test_it_will_not_derive_a_recommender_case_from_a_structured_output_transcript(): void
    {
        $history = ChatHistory::factory()->structuredOutput()->create();

        $this->assertNull(
            $this->extractor()->extract($history, 'v1', Agents::GuidelineRecommender),
            'A transformation transcript cannot stand in for a page-analysis run.'
        );
    }

    public function test_it_ignores_unsupported_agents(): void
    {
        $history = ChatHistory::factory()->recommender()->create([
            'agent_id' => Agent::firstOrCreate(['name' => Agents::ModelChatAgent->value])->id,
        ]);

        $this->assertNull($this->extractor()->extract($history, 'v1'));
    }

    public function test_stratified_sampling_spreads_across_strata(): void
    {
        $cases = collect();

        foreach (range(1, 10) as $index) {
            $cases->push(['tags' => ['agent' => 'A', 'outcome' => 'guidelines']]);
        }

        $cases->push(['tags' => ['agent' => 'A', 'outcome' => 'none']]);
        $cases->push(['tags' => ['agent' => 'A', 'outcome' => 'none']]);

        $sampled = $this->extractor()->sampleStratified($cases, 4);

        $this->assertCount(4, $sampled);
        $this->assertSame(
            2,
            $sampled->where('tags.outcome', 'none')->count(),
            'The rare stratum must not be crowded out.'
        );
    }

    public function test_it_drops_a_derived_case_duplicating_a_recorded_one(): void
    {
        $cases = collect([
            ['input' => ['data' => 'Same text.'], 'tags' => ['derived' => false]],
            ['input' => ['data' => 'Same text.'], 'tags' => ['derived' => true]],
            ['input' => ['data' => 'Other text.'], 'tags' => ['derived' => true]],
        ]);

        $unique = $this->extractor()->deduplicate($cases);

        $this->assertCount(2, $unique);
        $this->assertFalse($unique->first()['tags']['derived'], 'The recorded case is the one kept.');
    }

    public function test_it_returns_every_case_when_under_the_limit(): void
    {
        $cases = collect([['tags' => []], ['tags' => []]]);

        $this->assertCount(2, $this->extractor()->sampleStratified($cases, 5));
        $this->assertCount(2, $this->extractor()->sampleStratified($cases, null));
    }

    public function test_re_extraction_updates_rather_than_duplicates_a_case(): void
    {
        $history = ChatHistory::factory()->structuredOutput()->create();

        $this->extractor()->store($this->extractor()->extract($history, 'v1'));
        $this->extractor()->store($this->extractor()->extract($history, 'v1'));

        $this->assertSame(1, EvalCase::where('source_chat_history_ulid', $history->ulid)->count());
    }

    public function test_extracting_a_dataset_stores_sampled_cases_for_the_requested_agent_only(): void
    {
        ChatHistory::factory()->structuredOutput()->count(3)->create();
        ChatHistory::factory()->recommender()->create();

        $stored = $this->extractor()->extractDataset(Agents::StructuredOutput, 'test-dataset', 2);

        $cases = EvalCase::where('dataset_version', 'test-dataset')->get();

        $this->assertCount(2, $stored);
        $this->assertCount(2, $cases, 'Every sampled case is persisted, and only those.');
        $this->assertSame(
            [Agent::findAgent(Agents::StructuredOutput)->id],
            $cases->pluck('agent_id')->unique()->all()
        );
    }

    public function test_extracting_a_dataset_reports_why_transcripts_were_left_out(): void
    {
        ChatHistory::factory()->recommender()->create([
            'context_type' => Scope::class,
            'context_id' => Scope::factory()->create()->id,
        ]);
        $extractor = $this->extractor();

        $extractor->extractDataset(Agents::GuidelineRecommender, 'test-skips', null);

        $this->assertArrayHasKey('stored page content missing', $extractor->drainSkipReasons());
    }

    public function test_a_reviewed_only_dataset_keeps_just_the_cases_a_reviewer_triaged(): void
    {
        $triaged = $this->recommenderHistoryWithIssue($this->makeScopeWithPageContent(), AIStatus::Rejected);

        // A second scope on the same faked disk: the helper above resets it.
        $untriagedScope = Scope::factory()->create();
        $untriagedScope->setCurrentPage(Page::createPageContent($untriagedScope, $untriagedScope->url, '<html><body><p>Hi</p></body></html>'));
        $this->recommenderHistoryWithIssue($untriagedScope->refresh(), AIStatus::Generated);
        $extractor = $this->extractor();

        $stored = $extractor->extractDataset(Agents::GuidelineRecommender, 'test-reviewed', null, reviewedOnly: true);

        $this->assertSame([$triaged->ulid], $stored->pluck('source_chat_history_ulid')->all());
        $this->assertSame(1, $extractor->drainSkipReasons()['no reviewer verdicts'] ?? null);
    }

    /**
     * A replayable recommender run on `$scope` that produced one issue carrying `$status`.
     */
    private function recommenderHistoryWithIssue(Scope $scope, AIStatus $status): ChatHistory
    {
        $history = ChatHistory::factory()->recommender([11])->create([
            'context_type' => Scope::class,
            'context_id' => $scope->id,
        ]);

        Issue::factory()->create([
            'scope_id' => $scope->id,
            'project_id' => $scope->project_id,
            'guideline_id' => $this->makeGuideline()->id,
            'chat_history_ulid' => $history->ulid,
            'ai_status' => $status,
        ]);

        return $history;
    }

    private function extractor(): CaseExtractor
    {
        return app(CaseExtractor::class);
    }
}
