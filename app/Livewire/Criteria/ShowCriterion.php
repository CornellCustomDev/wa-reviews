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
                'guidelines' => $this->criterion->guidelines,
            ])
            ->layout('components.layouts.app', [
                'sidebar' => false,
                'breadcrumbs' => [
                    'Criteria' => route('criteria.index'),
                    $this->criterion->name => 'active'
                ],
            ]);
    }
}
