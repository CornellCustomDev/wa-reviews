<?php

namespace App\Livewire\Projects;

use App\Models\Report;
use Livewire\Attributes\On;
use Livewire\Component;

class ReportData extends Component
{
    public Report $report;

    #[On('report-updated')]
    public function refreshProject(): void
    {
        $this->report->project->refresh();
    }
}
