<?php

namespace App\Livewire\Projects;

use App\Enums\Roles;
use App\Models\Project;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class UpdateReviewer extends Component
{
    public Project $project;

    public $user;

    #[Computed]
    public function nonAssignedMembers(): array
    {
        return $this->project->team->users()
            ->get()->except($this->project->reviewer?->id ?? [])
            ->filter(fn ($user) => $user->hasRole([Roles::Reviewer, Roles::TeamAdmin], $this->project->team->id))
            ->all();
    }

    public function save()
    {
        $this->authorize('update-reviewer', [$this->project, User::find($this->user)]);

        // Validate that the user exists and is not already assigned to the project as a reviewer
        $validated = $this->validate([
            'user' => [
                'required',
                'exists:users,id',
                Rule::unique('project_assignments', 'user_id')
                    ->where('project_id', $this->project->id)
                    ->where('role', 'reviewer')
                    ->whereNull('deleted_at'),
            ],
        ]);

        $user = User::find($validated['user']);
        $this->project->assignToUser($user);

        unset($this->nonAssignedMembers);
        $this->project->refresh();

        $this->dispatch('close-update-reviewer');
    }

    #[On('reset-update-reviewer')]
    public function resetUpdateReviewer(): void
    {
        $this->user = null;
        $this->resetValidation();
    }
}
