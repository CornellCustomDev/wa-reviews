<?php

namespace App\Livewire\Scopes;

use App\Models\Scope;
use App\Models\SiteimproveRule;
use App\Services\SiteImprove\SiteimproveService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SiteimproveIssues extends Component
{
    public Scope $scope;

    #[Computed('siteimproveUrl')]
    public function siteimproveUrl(): string
    {
        $siteimproveService = SiteimproveService::fromScope($this->scope);

        return Cache::rememberForever(
            key: "siteimprove_url_{$this->scope->url}",
            callback: fn() => $siteimproveService->getPageReportUrl($this->scope->url) ?? ''
        );
    }

    #[Computed('siteimproveIssueCount')]
    public function siteimproveIssueCount(): string
    {
        $siteimproveService = SiteimproveService::fromScope($this->scope);

        return $siteimproveService->getPageIssuesCount($this->scope->url) ?? 0;
    }

    #[Computed('siteimproveIssues')]
    public function siteImproveIssues(): array
    {
        $siteimproveService = SiteimproveService::fromScope($this->scope);

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
}
