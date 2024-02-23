<?php

namespace App\Livewire\Guidelines;

use App\Models\Guideline;
use Livewire\Component;

class ViewGuidelines extends Component
{
    public function render()
    {
        return view('livewire.guidelines.view-guidelines', [
            'guidelines' => Guideline::all()
        ])->layout('components.layouts.app', [
            'sidebar' => false,
        ]);
    }
}
