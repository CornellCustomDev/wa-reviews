<?php

namespace App\Livewire\Scopes;

use App\Models\Guideline;
use App\Models\Issue;
use App\Models\Scope;
use App\Models\SiaRule;
use App\Services\SiteImprove\SiteimproveService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class SiteimproveIssues extends Component
{
    public Scope $scope;

    #[Computed('siteimproveUrl')]
    public function siteimproveUrl(): string
    {
        return SiteimproveService::getPageReportUrlForScope($this->scope);
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
    public function siaRelatedGuidelines(int $ruleId): ?Collection
    {
        return SiaRule::find($ruleId)?->actRule?->guidelines ?? collect();
    }

    public function siaRelatedIssues(int $ruleId): ?Issue
    {
        return $this->scope->issues->filter(fn($issue) => $issue->sia_rule_id === $ruleId)->first();
    }

    #[On('create-issue')]
    public function createIssue(SiaRule $rule, Guideline $guideline): void
    {
        $this->redirect(route('scope.issue.create', [
            'scope' => $this->scope,
            'rule' => $rule,
            'guideline' => $guideline,
        ]));
    }

    #[On('show-issue')]
    public function showIssue(Issue $issue): void
    {
        $this->redirect(route('issue.show', $issue));
    }

    #[On('issues-updated')]
    public function refreshIssues(): void
    {
        unset($this->siteImproveIssues);
    }
}
