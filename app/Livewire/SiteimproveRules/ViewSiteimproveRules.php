<?php

namespace App\Livewire\SiteimproveRules;

use App\Models\SiteimproveRule;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ViewSiteimproveRules extends Component
{
    public Collection $rules;

    public function mount(): void
    {
        $this->rules = SiteimproveRule::all()->groupBy('rule_id');
    }

    #[Computed('getRuleCategories')]
    public function getRules(): Collection
    {
        return SiteimproveRule::all()
            ->sortBy('rule_id')
            ->groupBy('rule_id');
    }

    public function getRuleIssue(Collection $rules): Collection
    {
        return SiteimproveRule::select(['rule_id', 'issues'])
            ->groupBy(['rule_id', 'issues'])
            ->get()
            ->keyBy('rule_id');
    }

    public function getGuidelinesByRule(): Collection
    {
        return SiteimproveRule::select(['rule_id', 'guidelines'])
            ->groupBy(['rule_id', 'guidelines'])
            ->get()
            ->keyBy('rule_id');
    }
}
