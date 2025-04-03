<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class AddReportViewer extends Component
{
    public Project $project;
    public $user;

    #[Computed]
    public function nonReportViewers(): array
    {
        return User::query()
            ->whereDoesntHave('teams', fn ($q) => $q->where('team_id', $this->project->team->id))
            // and not in report viewers already
            ->whereNotIn('id', $this->project->reportViewers->pluck('id'))
            ->get()->all();
    }

    public function save()
    {
        $this->authorize('manage-project', $this->project);

        $validated = $this->validate([
            'user' => [
                'required',
            ],
        ]);

        $user = User::find($validated['user']);
        $this->project->addReportViewer($user);

        unset($this->nonReportViewers);
        $this->dispatch('close-add-report-viewer');
    }

    #[On('refresh-report-viewers')]
    public function refreshReportViewers(): void
    {
        unset($this->nonReportViewers);
    }

    #[On('reset-add-report-viewer')]
    public function resetAddReportReviewer(): void
    {
        $this->user = null;
        $this->resetValidation();
    }
}
