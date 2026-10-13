<?php

namespace Tests\Feature\Livewire;

use App\Enums\Roles;
use App\Livewire\Ai\ScopeChat;
use App\Livewire\Issues\IssueAnalyzer;
use App\Livewire\Issues\IssueFormAnalyzer;
use App\Livewire\Scopes\ScopeAnalyzer;
use App\Models\Guideline;
use App\Models\Issue;
use App\Models\Item;
use App\Models\Page;
use App\Models\Project;
use App\Models\Scope;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Prism\Prism\Facades\Prism;
use Tests\Feature\FeatureTestCase;

/**
 * An oversized page must be refused before a model is called; the gateway would
 * otherwise fail the request mid-stream with a context-window error.
 *
 * @see docs/oversized-page-content.md
 */
class OversizedPageContentTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeSiteimproveService();
        Storage::fake();
        config()->set('cornell_ai.page_content_char_limit', 1000);
    }

    #[Test]
    public function an_oversized_page_is_refused_before_the_model_is_called(): void
    {
        // An empty fake means any model call at all fails the test.
        Prism::fake([]);

        Livewire::test(ScopeAnalyzer::class, ['scope' => $this->scopeWithPageOfSize(5000)])
            ->call('recommendGuidelines')
            ->assertSet('showFeedback', true)
            // `initiateAction()` would have set this, so it was never reached.
            ->assertSet('streaming', false)
            ->assertSee('too large for AI analysis');
    }

    #[Test]
    public function the_issue_analyzer_refuses_an_oversized_page_without_deleting_existing_items(): void
    {
        Prism::fake([]);
        $scope = $this->scopeWithPageOfSize(5000);
        $issue = Issue::factory()->for($scope)->create(['project_id' => $scope->project_id]);
        $guidelineId = Guideline::max('id') + 1;
        Guideline::factory()->create(['id' => $guidelineId]);
        $item = Item::factory()->for($issue)->create(['guideline_id' => $guidelineId]);

        Livewire::test(IssueAnalyzer::class, ['issue' => $issue])
            ->call('recommendGuidelines')
            ->assertSet('showFeedback', true)
            ->assertSet('streaming', false)
            ->assertSee('too large for AI analysis');

        $this->assertModelExists($item);
    }

    #[Test]
    public function the_issue_form_analyzer_refuses_an_oversized_page_without_clearing_recommendations(): void
    {
        Prism::fake([]);
        $recommendations = collect([['guideline_id' => 1, 'description' => 'An earlier recommendation.']]);

        Livewire::test(IssueFormAnalyzer::class, ['scope' => $this->scopeWithPageOfSize(5000)])
            ->set('recommendations', $recommendations)
            ->call('recommendGuidelines', 'The search button', 'It has no accessible name.')
            ->assertSet('showFeedback', true)
            ->assertSet('streaming', false)
            ->assertSee('too large for AI analysis')
            ->assertSet('recommendations', $recommendations);
    }

    #[Test]
    public function a_page_within_the_limit_still_starts_the_stream(): void
    {
        Livewire::test(ScopeAnalyzer::class, ['scope' => $this->scopeWithPageOfSize(100)])
            ->call('recommendGuidelines')
            ->assertSet('showFeedback', false)
            ->assertSet('streaming', true);
    }

    #[Test]
    public function the_chat_refuses_an_oversized_page_it_would_otherwise_inline(): void
    {
        Livewire::test(ScopeChat::class, [
            'scope' => $this->scopeWithPageOfSize(5000),
            'chatMessages' => [],
        ])
            ->set('userMessage', 'Is this page accessible?')
            ->call('sendChatMessage')
            ->assertSee('too large for AI analysis')
            ->assertSet('userMessage', 'Is this page accessible?', 'The unsent message must survive the refusal.');
    }

    #[Test]
    public function the_size_is_measured_without_reading_the_content(): void
    {
        $scope = $this->scopeWithPageOfSize(2048);

        $this->assertSame(2048, $scope->getPageContentSize());
        $this->assertTrue($scope->pageContentExceedsAnalysisLimit());

        config()->set('cornell_ai.page_content_char_limit', 4096);
        $this->assertFalse($scope->pageContentExceedsAnalysisLimit());
        $this->assertNull($scope->pageContentAnalysisRefusal());
    }

    #[Test]
    public function a_scope_with_no_retrieved_page_is_not_treated_as_oversized(): void
    {
        $this->assertFalse(Scope::factory()->create()->pageContentExceedsAnalysisLimit());
    }

    private function scopeWithPageOfSize(int $bytes): Scope
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create(['team_id' => $user->teams()->first()->id]);
        $project->assignToUser($user);

        $scope = Scope::factory()->create(['project_id' => $project->id]);
        // `latestPage` matches on the scope's own url, so the page must share it.
        $scope->setCurrentPage(Page::createPageContent($scope, $scope->url, str_repeat('x', $bytes)));

        return $scope->refresh();
    }
}
