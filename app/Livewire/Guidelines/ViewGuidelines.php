<?php

namespace App\Livewire\Guidelines;

use App\Models\Guideline;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ViewGuidelines extends Component
{
    #[Computed]
    public function guidelines(): Collection
    {
        return Guideline::all();
    }
}
