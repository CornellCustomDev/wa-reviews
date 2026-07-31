<?php

namespace App\Livewire\Reports;

use App\Models\Project;
use App\Models\Report;
use App\Models\Scope;
use App\Services\ProjectWorkflowService;
use App\Services\SiteImprove\SiteimproveService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ShowReport extends Component
{
    public Report $report;
    public ?string $selectedImage = null;

    #[Computed]
    public function project(): Project
    {
        return $this->report->project;
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
        $this->authorize('complete-report', $this->report);

        $this->report->completeReport();
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
        $this->authorize('view', $this->report);

        return view('livewire.reports.show-report')
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
