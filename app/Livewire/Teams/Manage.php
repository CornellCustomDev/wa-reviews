<?php

namespace App\Livewire\Teams;

use Livewire\Attributes\Url;
use Livewire\Component;

class Manage extends Component
{
    #[Url]
    public string $tab = 'teams';
}
