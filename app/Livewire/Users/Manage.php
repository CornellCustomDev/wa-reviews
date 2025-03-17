<?php

namespace App\Livewire\Users;

use Livewire\Attributes\Url;
use Livewire\Component;

class Manage extends Component
{
    #[Url]
    public string $tab = 'users';
}
