<?php

namespace App\Livewire\Users;

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

        // TODO: This should just close the modal and refresh the content, not redirect
        return redirect()->route('users.manage');
    }
}
