<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Models\Guideline;
use App\Models\Issue;
use App\Models\Project;
use App\Services\GoogleApi\GoogleService;
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\AppTesters;
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\CUAuth;
use PHPUnit\Framework\Attributes\Test;

class ReportGoogleControllerTest extends FeatureTestCase
{
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([AppTesters::class, CUAuth::class]);

        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $this->project = Project::factory()->create(['team_id' => $user->teams()->first()->id]);
        $this->project->assignToUser($user);
    }

    #[Test]
    public function redirects_to_the_report_with_a_warning_when_links_are_invalid(): void
    {
        $issue = Issue::factory()->create([
            'project_id' => $this->project->id,
            'guideline_id' => $this->createGuidelineId(),
            'recommendation' => '<p><a href="mailto:a@b.edu&quot;&gt;a@b.edu&lt;/a&gt;">Someone</a></p>',
        ]);
        $this->mock(GoogleService::class)->shouldNotReceive('ensureAuthorized');

        $this->get(route('project.report.google', $this->project))
            ->assertRedirect(route('project.report', $this->project))
            ->assertSessionHas('warning.details', ['Issue '.$issue->getGuidelineInstanceNumber().', Recommendations: "Someone"']);

        $this->get(route('project.report', $this->project))
            ->assertSee('This report can’t be exported to Google Sheets until these invalid links are fixed:')
            ->assertSee('Recommendations: &quot;Someone&quot;', false);
    }

    #[Test]
    public function continues_to_google_when_links_are_valid(): void
    {
        Issue::factory()->create([
            'project_id' => $this->project->id,
            'guideline_id' => $this->createGuidelineId(),
            'recommendation' => '<p><a href="mailto:a@b.edu">Someone</a></p>',
        ]);
        $this->mock(GoogleService::class, function ($mock) {
            $mock->shouldReceive('ensureAuthorized')->once()->andReturn(false);
            $mock->shouldReceive('getAuthUrl')->andReturn('https://accounts.google.com/o/oauth2/auth');
        });

        $this->get(route('project.report.google', $this->project))
            ->assertRedirect('https://accounts.google.com/o/oauth2/auth')
            ->assertSessionMissing('warning');
    }

    /**
     * Guideline IDs aren't auto-incrementing, so assign the next one. The model would report the insert ID (0)
     * after creating, so return the assigned ID instead.
     */
    private function createGuidelineId(): int
    {
        $id = Guideline::max('id') + 1;
        Guideline::factory()->create(['id' => $id]);

        return $id;
    }
}
