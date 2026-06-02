<?php

namespace App\Livewire\Projects;

use App\Events\ProjectChanged;
use App\Livewire\Forms\ReportForm;
use App\Models\Project;
use App\Models\Scope;
use App\Services\SiteImprove\SiteimproveService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Report extends Component
{
    public Project $project;
    public ReportForm $form;
    public bool $showEdit = false;
    public ?string $selectedImage = null;

    public function mount(Project $project): void
    {
        $this->form->setModel($project);
    }

    #[Computed]
    public function issues()
    {
        return $this->project->getReportableIssues()
            ->groupBy('scope_id')
            ->sortKeys();
    }

    #[Computed(persist: true)]
    public function siteimproveUrl(Scope $scope): string
    {
        return SiteimproveService::getPageReportUrlForScope($scope);
    }

    public function saveReport(): void
    {
        $this->authorize('update', $this->project);
        $this->form->update();
        $this->showEdit = false;
        $this->project->refresh();
    }

    public function completeReview(): void
    {
        $this->authorize('update-status', $this->project);
        $this->showEdit = true;
        $this->form->validateForCompletion();
        $this->form->update();

        $nextStatus = $this->project->status->nextStatus();
        $this->project->update([
            'status' => $nextStatus,
            'completed_at' => $nextStatus->isReviewComplete()
                ? now()
                : $this->project->completed_at,
        ]);

        event(new ProjectChanged($this->project, 'status changed'));

        $this->redirect(route('project.show', $this->project), navigate: true);
    }

    public function viewImage(string $imageUrl): void
    {
        $this->selectedImage = $imageUrl;
        $this->modal('view-image')->show();
    }

    public function closeImage(): void
    {
        $this->modal('view-image')->close();
        $this->selectedImage = null;
    }

    public function render()
    {
        $this->authorize('view', $this->project);

        return view('livewire.projects.report')
            ->layout('components.layouts.app', [
                'breadcrumbs' => $this->getBreadcrumbs(),
            ]);
    }

    protected function getBreadcrumbs(): array
    {
        return [
            'Projects' => route('projects'),
            $this->project->name => route('project.show', $this->project),
            'Report' => 'active',
        ];
    }
}
