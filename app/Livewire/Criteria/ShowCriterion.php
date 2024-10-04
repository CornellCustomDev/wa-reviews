<?php

namespace App\Livewire\Criteria;

use App\Models\Criterion;
use Livewire\Component;

class ShowCriterion extends Component
{
    public Criterion $criterion;

    public function render()
    {
        return view('livewire.criteria.show-criterion', [
                'actRules' => $this->criterion->actRules()->get()->sortBy('name'),
            ])
            ->layout('components.layouts.app', [
                'breadcrumbs' => [
                    'Criteria' => route('criteria.index'),
                    $this->criterion->name => 'active'
                ],
            ]);
    }
}
