<?php

namespace App\Livewire\Scopes;

use App\Models\Scope;
use App\Models\SiteimproveRule;
use App\Services\SiteImprove\SiteimproveService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ShowScope extends Component
{
    public Scope $scope;

    #[Computed('siteimproveUrl')]
    public function siteimproveUrl(): string
    {
        // TODO - Do not reach in to get the siteimprove_id from project
        $siteId = $this->scope->project->siteimprove_id;
        $siteimproveService = SiteimproveService::make($siteId);

        return Cache::rememberForever(
            key: "siteimprove_url_{$this->scope->url}",
            callback: fn() => $siteimproveService->getPageReportUrl($this->scope->url) ?? ''
        );
    }

    #[Computed('siteimproveIssueCount')]
    public function siteimproveIssueCount(): string
    {
        $siteId = $this->scope->project->siteimprove_id;
        $siteimproveService = SiteimproveService::make($siteId);

        return $siteimproveService->getPageIssuesCount($this->scope->url) ?? 0;
    }

    #[Computed('siteimproveIssues')]
    public function siteImproveIssues(): array
    {
        $siteId = $this->scope->project->siteimprove_id;
        $siteimproveService = SiteimproveService::make($siteId);

        return $siteimproveService->getPageIssues($this->scope->url) ?? [];
    }

    #[Computed]
    public function siteimproveRelatedGuidelines($ruleId): Collection
    {
        // only get the rules that have criteria
        $rules = SiteimproveRule::where('rule_id', $ruleId)
            ->whereHas('criterion')
            ->get();

        // For each rule, get the criteria, for each criterion, get the guidelines as objects
        return $rules->map(fn($rule) => $rule->criterion->guidelines)->flatten();
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
