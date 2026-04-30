<?php

namespace App\Livewire\Projects;

use App\Enums\Roles;
use App\Models\Project;
use App\Models\ProjectAssignment;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class UpdateVerifier extends Component
{
    public Project $project;

    public $user;

    #[Computed]
    public function nonAssignedMembers(): array
    {
        return $this->project->team->users()
            ->get()->except($this->project->verifier?->id ?? [])
            ->filter(fn ($user) => $user->hasRole([Roles::Reviewer, Roles::TeamAdmin], $this->project->team->id))
            ->all();
    }

    public function save(): void
    {
        $this->authorize('update-verifier', [$this->project, User::find($this->user)]);

        // Validate that the user exists and is not already assigned to the project as verifier
        $validated = $this->validate([
            'user' => [
                'required',
                'exists:users,id',
                Rule::unique('project_assignments', 'user_id')
                    ->where('project_id', $this->project->id)
                    ->where('role', ProjectAssignment::VERIFIER)
                    ->whereNull('deleted_at'),
            ],
        ]);

        $user = User::find($validated['user']);
        $this->project->assignVerifier($user);

        unset($this->nonAssignedMembers);
        $this->project->refresh();

        $this->dispatch('close-update-verifier');
    }

    #[On('reset-update-verifier')]
    public function resetUpdateVerifier(): void
    {
        $this->user = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.projects.update-verifier');
    }
}
