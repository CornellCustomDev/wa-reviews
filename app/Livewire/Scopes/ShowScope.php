<?php

namespace App\Livewire\Scopes;

use App\Models\Scope;
use App\Models\SiteimproveRule;
use App\Services\SiteImprove\SiteimproveService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class ShowScope extends Component
{
    public Scope $scope;

    #[Url]
    public string $tab = 'issues';

    #[Computed('siteimproveIssueCount')]
    public function siteimproveIssueCount(): string
    {
        $siteimproveService = SiteimproveService::fromScope($this->scope);

        return $siteimproveService->getPageIssuesCount($this->scope->url) ?? 0;
    }

    #[On('issues-updated')]
    public function refreshScope(): void
    {
        $this->scope->refresh();
    }

    public function render()
    {
        $this->authorize('view', $this->scope);

        return view('livewire.scopes.show-scope')
            ->layout('components.layouts.app', [
                'sidebar' => true,
                'breadcrumbs' => $this->getBreadcrumbs(),
            ]);
    }

    protected function getBreadcrumbs(): array
    {
        return [
            'Projects' => route('projects'),
            $this->scope->project->name => route('project.show', $this->scope->project),
            $this->scope->title => 'active'
        ];
    }
}
