<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Attributes\On;
use Livewire\Component;

class ReportData extends Component
{
    public Project $project;

    #[On('report-updated')]
    public function refreshProject(): void
    {
        $this->project->refresh();
    }
}
