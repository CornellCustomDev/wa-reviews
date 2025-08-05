<?php

namespace App\Exports;

use App\Models\Project;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ProjectReportExport implements FromView
{
    public function __construct(
        public readonly Project $project
    ) {
    }

    public function view(): View
    {
        $issues = $this->project->getReportableIssues();
        return view('exports.project-report', [
            'project' => $this->project,
            'issues' => $issues,
//            'issuesByScope' => $issues->groupBy('scope_id'),
            'format' => 'xlsx',
        ]);
    }
}
