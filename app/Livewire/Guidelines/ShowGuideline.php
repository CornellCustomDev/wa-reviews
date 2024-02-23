<?php

namespace App\Livewire\Guidelines;

use App\Models\Guideline;
use Livewire\Component;

class ShowGuideline extends Component
{
    public Guideline $guideline;

    public function render()
    {
        return view('livewire.guidelines.show-guideline')
            ->layout('components.layouts.app', [
                'sidebar' => false,
                'breadcrumbs' => [
                    'Guidelines' => route('guidelines.index'),
                    $this->guideline->name => 'active'
                ],
            ]);
    }
}
