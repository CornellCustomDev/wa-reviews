<?php

namespace App\Livewire\Teams;

use App\Livewire\Forms\TeamForm;
use App\Models\Team;
use Livewire\Component;

class CreateTeam extends Component
{
    public TeamForm $form;

    public function save()
    {
        $this->authorize('create', Team::class);
        $this->form->store();

        $this->dispatch('close-edit-team');
        $this->dispatch('team-changes');
    }
}
