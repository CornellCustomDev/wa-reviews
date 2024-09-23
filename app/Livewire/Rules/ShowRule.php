<?php

namespace App\Livewire\Rules;

use App\Models\ActRule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ShowRule extends Component
{
    public ActRule $rule;
}
