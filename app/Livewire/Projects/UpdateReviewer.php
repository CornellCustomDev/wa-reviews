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

    #[On('team-changes')]
    public function teamChanges(): void
    {
        unset($this->nonAssignedMembers);
        $this->project->refresh();
    }

    public function save()
    {
        $this->authorize('manage-project', $this->project);

        // Validate that the user exists and is not already on the team
        $validated = $this->validate([
            'user' => [
                'required',
                Rule::unique('project_assignments', 'user_id')
                    ->where('project_id', $this->project->id)
                    ->whereNull('deleted_at')
            ],
        ]);

        $user = User::find($validated['user']);
        $this->project->assignToUser($user);
        $this->teamChanges();

        $this->dispatch('close-update-reviewer');
    }

    #[On('reset-update-reviewer')]
    public function resetUpdateReviewer(): void
    {
        $this->user = null;
        $this->resetValidation();
    }
}
