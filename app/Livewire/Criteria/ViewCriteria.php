<?php

namespace App\Livewire\Criteria;

use App\Models\Criterion;
use Livewire\Component;

class ViewCriteria extends Component
{
    public function render()
    {
        return view('livewire.criteria.view-criteria', [
            'criteria' => Criterion::all()
        ])->layout('components.layouts.app', [
            'sidebar' => false,
        ]);
    }
}
