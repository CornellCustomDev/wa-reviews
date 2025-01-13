<?php

namespace App\Livewire\Criteria;

use App\Models\Criterion;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ViewCriteria extends Component
{
    #[Computed('criteria')]
    public function criteria(): Collection
    {
        return Criterion::all()->sortBy('number');
    }
}
