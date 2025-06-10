<?php

namespace App\Livewire\Issues;

use App\Livewire\Features\SupportFileUploads\WithMultipleFileUploads;
use App\Livewire\Forms\IssueForm;
use App\Models\Issue;
use App\Models\Project;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CreateProjectIssue extends Component
{
    use WithMultipleFileUploads;

    public IssueForm $form;
    public Project $project;

    public function save()
    {
        $this->authorize('create', [Issue::class, $this->project]);
        $issue = $this->form->store($this->project);

        return redirect()->route('issue.show', $issue);
    }

    #[Computed(persist: true)]
    public function getGuidelinesOptions()
    {
        return $this->form->getGuidelineSelectArray();
    }

    public function render()
    {
        return view('livewire.issues.create-project-issue')
            ->layout('components.layouts.app', [
                'sidebar' => true,
                'breadcrumbs' => $this->getBreadcrumbs(),
            ]);
    }

    protected function getBreadcrumbs(): array
    {
        return [
            'Projects' => route('projects'),
            $this->project->name => route('project.show', $this->project),
            'Add Issue' => 'active'
        ];
    }

    #[Computed]
    public function scopeOptions(): Collection
    {
        return isset($this->project)
            ? $this->project->scopes()->get()
                ->map(fn($scope) => [
                    'value' => $scope->id,
                    'option' => $scope->title,
                ])
            : collect();
    }

    public function addScope(): void
    {
        Flux::modal('add-scope')->show();
    }

    #[On('refresh-scopes')]
    public function refreshScopes($scope_id = null): void
    {
        if ($scope_id) {
            $this->form->scope_id = $scope_id;
        }
        unset($this->scopeOptions);
    }
}
