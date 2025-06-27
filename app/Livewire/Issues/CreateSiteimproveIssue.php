<?php

namespace App\Livewire\Issues;

use App\Livewire\Forms\IssueForm;
use App\Models\Guideline;
use App\Models\Scope;
use App\Models\SiaRule;
use App\Services\SiteImprove\SiteimproveService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CreateSiteimproveIssue extends Component
{
    public IssueForm $form;
    public Scope $scope;
    public SiaRule $rule;
    public Guideline $guideline;

    public function mount(Scope $scope, SiaRule $rule, Guideline $guideline)
    {
        $this->scope = $scope;
        $this->rule = $rule;
        $this->guideline = $guideline;
        // Get the description from SiteimproveRule
        // $siteimproveRule = $this->rule->siteimproveRules->firstWhere('criterion_id', $this->guideline->criterion_id);
        $this->form->description = $this->rule->name_html; // $siteimproveRule->issues;
    }

    #[Computed('siteimproveUrl')]
    public function siteimproveUrl(): string
    {
        return SiteimproveService::getPageReportUrlForScope($this->scope);
    }

    public function save()
    {
        $this->authorize('update', $this->scope);
        $issue = $this->form->store($this->scope, $this->rule);

        return redirect()->route('issue.show', $issue);
    }

}
