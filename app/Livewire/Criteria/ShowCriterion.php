<?php

namespace App\Livewire\Criteria;

use App\Models\Criterion;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ShowCriterion extends Component
{
    public Criterion $criterion;

    #[Computed]
    public function guidelines()
    {
        return $this->criterion->guidelines;
    }

    #[Computed]
    public function actRules()
    {
        return $this->criterion->actRules()->get()->sortBy('name');
    }

    #[Computed]
    public function siteimproveRules()
    {
        return $this->criterion->siteimproveRules()->get()->sortBy('rule_id');
    }

    #[Computed]
    public function siaRules()
    {
        return $this->criterion->siaRules()->get()->sortBy('alfa');
    }

    public function render()
    {
        return view('livewire.criteria.show-criterion')
            ->layout('components.layouts.app', [
                'breadcrumbs' => [
                    'Criteria' => route('criteria.index'),
                    $this->criterion->name => 'active'
                ],
            ]);
    }
}
