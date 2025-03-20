<?php

namespace App\Livewire\Forms;

use App\Models\Team;
use Livewire\Attributes\Validate;
use Livewire\Form;

class TeamForm extends Form
{
    public ?Team $team;

    #[Validate('required|string|min:3|max:255', as: 'Team Name')]
    public string $name = '';

    public function setModel(Team $team): void
    {
        $this->team = $team;
        $this->name = $team->name;
    }

    public function getModel(): Team
    {
        return $this->team;
    }

    public function store(): Team
    {
        $this->validate();

        $this->team = Team::create($this->all());

        return $this->team;
    }

    public function update(): void
    {
        $this->validate();

        $this->team->update($this->all());
    }
}
