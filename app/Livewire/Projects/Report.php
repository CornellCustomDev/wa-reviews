<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\Report as ReportModel;
use App\Models\Scope;
use App\Services\ProjectWorkflowService;
use App\Services\SiteImprove\SiteimproveService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Report extends Component
{
    public Project $project;
    public ?string $selectedImage = null;

    #[Computed]
    public function report(): ReportModel
    {
        if ($this->project->status->isInVerification() || $this->project->status->isClosed()) {
            return $this->project->getVerificationReport() ?? $this->project->getReviewReport();
        }

        return $this->project->getReviewReport();
    }

    #[Computed]
    public function issues()
    {
        return $this->report->reportableIssues()
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

    public function completeReport(ProjectWorkflowService $projectWorkflow): void
    {
        $reviewReport = $this->project->getReviewReport();

        $this->authorize('complete-report', $reviewReport);

        $reviewReport->completeReport();
        $projectWorkflow->completeReview($this->project);

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

        return view('livewire.projects.report', ['report' => $this->report])
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
