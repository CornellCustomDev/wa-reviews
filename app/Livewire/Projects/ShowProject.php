<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Services\SiteImprove\SiteimproveService;
use Exception;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class ShowProject extends Component
{
    public Project $project;

    #[Url]
    public string $tab = 'scope';
    #[Url(as: 'e', history: true)]
    public bool $showEdit = false;

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

    public function updated($name, $value): void
    {
        if ($name !== 'showEdit') {
            $this->showEdit = false;
        }
    }

    #[On('refresh-project')]
    public function refreshProject(): void
    {
        // @TODO Figure out how to get the child elements to refresh instead using this redirect
        $this->redirect(route('project.show', [
            'project' => $this->project,
            'tab' => $this->tab,
        ]));
    }

    public function render()
    {
        $this->authorize('view', $this->project);
        if ($this->showEdit === true && !Gate::allows('update', $this->project)) {
            $this->showEdit = false;
        }

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
