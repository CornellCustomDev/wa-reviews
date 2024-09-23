<?php

namespace App\Livewire\Criteria;

use App\Models\Criterion;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ViewCriteria extends Component
{
    public function render()
    {
        return view('livewire.criteria.view-criteria', [
            'criteria' => Criterion::all()
        ]);
    }
}
