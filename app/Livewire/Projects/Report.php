<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\Scope;
use App\Services\SiteImprove\SiteimproveService;
use Livewire\Attributes\Computed;
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
