<?php

namespace App\Livewire\Teams;

use Livewire\Attributes\Url;
use Livewire\Component;

class Manage extends Component
{
    #[Url]
    public string $tab = 'teams';

    /** redirect to the team page the user is only on one team */
    public function mount(): void
    {
        if (! auth()->user()->isAdministrator() && auth()->user()->teams->count() === 1) {
            $this->redirectRoute('teams.show', auth()->user()->teams->first());
        }
    }
}
