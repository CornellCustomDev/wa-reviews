<?php

namespace App\Livewire\ActRules;

use App\Models\ActRule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ShowRule extends Component
{
    public ActRule $rule;
}
