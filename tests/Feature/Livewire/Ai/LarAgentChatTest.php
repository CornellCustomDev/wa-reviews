<?php

namespace Tests\Feature\Livewire\Ai;

use App\Enums\Roles;
use App\Livewire\Scopes\ScopeChat;
use App\Models\Project;
use App\Models\Scope;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

class LarAgentChatTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeSiteimproveService();

        // None of these cases should ever reach the LLM; fail fast if one does.
        Http::preventStrayRequests();
    }

    protected function makeScope(): Scope
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create(['team_id' => $team->id]);
        $project->assignToUser($user);

        return Scope::factory()->create(['project_id' => $project->id]);
    }

    /**
     * A blank message must not reach the agent: LarAgent's respondStreamed()
     * silently drops falsy messages and then rejects the resulting null prompt.
     */
    #[Test]
    public function streaming_a_blank_message_does_not_reach_the_agent()
    {
        Livewire::test(ScopeChat::class, ['scope' => $this->makeScope()])
            ->set('userMessage', '')
            ->call('streamUserMessage')
            ->assertSet('showFeedback', true)
            ->assertSet('streaming', false)
            ->assertSee('Enter a message before sending.');
    }

    #[Test]
    public function sending_a_blank_message_does_not_start_streaming()
    {
        Livewire::test(ScopeChat::class, ['scope' => $this->makeScope()])
            ->set('userMessage', '')
            ->call('sendUserMessage')
            ->assertSet('streaming', false)
            ->assertSet('showFeedback', true);
    }

    /**
     * Guards the re-entrancy that produced the production TypeError: a second
     * send arriving after the first had already cleared $userMessage. The refusal
     * must be visible, so a stuck streaming flag cannot lock the chat silently.
     */
    #[Test]
    public function sending_while_already_streaming_reports_that_a_response_is_pending()
    {
        Livewire::test(ScopeChat::class, ['scope' => $this->makeScope()])
            ->set('userMessage', 'Some question')
            ->set('streaming', true)
            ->call('sendUserMessage')
            ->assertSet('showFeedback', true)
            ->assertSee('A response is already in progress.')
            ->assertSet('userMessage', 'Some question');
    }
}
