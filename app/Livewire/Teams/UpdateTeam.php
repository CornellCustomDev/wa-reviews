<?php

namespace App\Livewire\Teams;

use App\Livewire\Forms\TeamForm;
use App\Models\Team;
use Livewire\Component;

class UpdateTeam extends Component
{
    public TeamForm $form;

    public function mount(Team $team)
    {
        $this->form->setModel($team);
    }

    public function save()
    {
        $this->authorize('update', $this->form->team);
        $this->form->update();

        $this->dispatch('close-edit-team');
        $this->dispatch('team-changes');
    }
}
