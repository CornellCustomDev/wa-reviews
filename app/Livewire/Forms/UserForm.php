<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Livewire\Attributes\Validate;
use Livewire\Form;

class UserForm extends Form
{
    public ?User $user;

    #[Validate('required|string|max:255')]
    public string $name = '';
    #[Validate('nullable')]
    public array $teams = [];

    public function setModel(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->teams = $user->teams()->pluck('teams.id')->all() ?? [];
    }

    public function getModel(): User
    {
        return $this->user;
    }

    public function store(): User
    {
        $this->validate();

        $this->user = User::create($this->all());

        return $this->user;
    }

    public function update(): void
    {
        $this->validate();

        $this->user->update($this->except('teams'));
        $this->user->teams()->sync($this->teams);
    }
}
