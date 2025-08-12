<?php

namespace App\Http\Controllers;

use App\Models\Project;

class ReportRawController extends Controller
{
    public function __invoke(Project $project)
    {
        return view('exports.project-report', [
            'project' => $project,
            'issues' => $project->getReportableIssues(),
            'format' => 'raw',
        ]);
    }
}
