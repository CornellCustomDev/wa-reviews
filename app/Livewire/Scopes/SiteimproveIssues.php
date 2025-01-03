<?php

namespace App\Livewire\Scopes;

use App\Models\Scope;
use App\Models\SiaRule;
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
    public function siteimproveRelatedGuidelines(int $ruleId): ?Collection
    {
        return SiaRule::find($ruleId)?->actRule?->guidelines ?? collect();
    }
}
