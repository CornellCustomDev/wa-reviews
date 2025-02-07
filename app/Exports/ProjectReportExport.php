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
        return view('exports.project-report', [
            'project' => $this->project,
            'issuesByScope' => $this->project->issues()->with(['scope', 'items'])->get()->groupBy('scope_id'),
        ]);
    }
}
