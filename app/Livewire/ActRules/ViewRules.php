<?php

namespace App\Livewire\ActRules;

use App\Models\ActRule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ViewRules extends Component
{
    public function render()
    {
        return view('livewire.act-rules.view-rules', [
            'rules' => ActRule::all()->sortBy('name')
        ]);
    }
}
