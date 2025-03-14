<?php

namespace App\Livewire\Users;

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

        // TODO: This should just close the modal and refresh the content, not redirect
        return redirect()->route('users.manage');
    }
}
