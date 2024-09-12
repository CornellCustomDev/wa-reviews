<?php

namespace App\Livewire\Guidelines;

use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Str;

#[Layout('components.layouts.app')]
class Doc extends Component
{
    public function render()
    {
        return view('livewire.guidelines.doc', [
            'guidelines' => Str::of(Storage::get('guidelines.md'))->markdown(),
        ]);
    }
}
