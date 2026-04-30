<?php

namespace Tests\Feature\Livewire;

use App\Enums\IssueStatus;
use App\Enums\ProjectStatus;
use App\Enums\Roles;
use App\Livewire\Forms\IssueForm;
use App\Models\Project;
use App\Models\Scope;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

class IssueFormTest extends FeatureTestCase
{
    #[Test] public function store_saves_issue(): void
    {
        $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create(['status' => ProjectStatus::InProgress]);
        $scope = Scope::factory()->create(['project_id' => $project->id]);

        $component = Livewire::test(ComponentWithForm::class, ['formClass' => IssueForm::class])
            ->set('form.scope_id', $scope->id)
            ->set('form.target', 'https://example.com/page')
            ->set('form.description', 'Missing alt text on image.');

        $issue = $component->instance()->form->store($project);

        $this->assertEmpty($issue->status);
    }

    #[Test] public function store_sets_new_issue_status_when_project_is_in_verification(): void
    {
        $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create(['status' => ProjectStatus::VerificationReview]);
        $scope = Scope::factory()->create(['project_id' => $project->id]);

        $component = Livewire::test(ComponentWithForm::class, ['formClass' => IssueForm::class])
            ->set('form.scope_id', $scope->id)
            ->set('form.target', 'https://example.com/page')
            ->set('form.description', 'Missing alt text on image.');

        $issue = $component->instance()->form->store($project);

        $this->assertEquals(IssueStatus::NewIssue, $issue->status);
    }
}
