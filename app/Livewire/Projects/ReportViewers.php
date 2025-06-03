<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

class ReportViewers extends Component
{
    public Project $project;

    public function removeReportViewer(User $user): void
    {
        $this->authorize('update-report-viewers', $this->project);

        $this->project->removeReportViewer($user);

        $this->dispatch('refresh-report-viewers');
    }

    #[On('close-add-report-viewer')]
    public function closeAddReportViewer(): void
    {
        $this->modal('add-report-viewer')->close();
        $this->dispatch('reset-add-report-viewer');
    }
}
