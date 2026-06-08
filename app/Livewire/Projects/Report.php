<?php

namespace App\Livewire\Projects;

use App\Enums\ProjectStatus;
use App\Events\ProjectChanged;
use App\Models\Project;
use App\Models\Scope;
use App\Services\SiteImprove\SiteimproveService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Report extends Component
{
    public Project $project;
    public ?string $selectedImage = null;

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

    #[On('report-updated')]
    public function refreshProject(): void
    {
        $this->project->refresh();
    }

    public function completeReview(): void
    {
        $this->authorize('complete-report', $this->project);

        $report = $this->project->getReviewReport();
        $report->addIssuesToReport();
        $report->update([
            'completed_by' => auth()->id(),
            'completed_at' => now(),
        ]);

        $this->project->update([
            'status' => ProjectStatus::ReviewComplete,
            'completed_at' => $this->project->completed_at ?? now(),
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
