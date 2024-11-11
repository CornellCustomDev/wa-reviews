<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Services\SiteImprove\SiteimproveService;
use Exception;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ShowProject extends Component
{
    public Project $project;

    #[Computed('siteimprovePagesWithIssues')]
    public function siteimprovePagesWithIssues(): array
    {
        $siteId = $this->project->siteimprove_id;
        $siteimproveService = SiteimproveService::make($siteId);

        try {
            $pages = $siteimproveService->getPagesWithIssues();
        } catch (Exception) {
            $pages = [];
        }

        return $pages;
    }

    public function render()
    {
        $this->authorize('view', $this->project);
        return view('livewire.projects.show-project')
            ->layout('components.layouts.app', [
                'breadcrumbs' => $this->getBreadcrumbs(),
            ]);
    }

    protected function getBreadcrumbs(): array
    {
        return [
            'Projects' => route('projects'),
            $this->project->name => 'active',
        ];
    }

}
