<?php

namespace App\Livewire\Scopes;

use App\Livewire\Forms\ScopeForm;
use App\Models\Scope;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class UpdateScope extends Component
{
    public ScopeForm $form;

    public function mount(Scope $scope)
    {
        $this->form->setModel($scope);
    }

    public function save()
    {
        $this->authorize('update', $this->form->scope);
        $this->form->update();

        return redirect()->route('scope.show', $this->form->scope);
    }
}
