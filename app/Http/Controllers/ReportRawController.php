<?php

namespace App\Http\Controllers;

use App\Models\Project;

class ReportRawController extends Controller
{
    public function __invoke(Project $project)
    {
        $report = $project->getReviewReport();

        return view('exports.project-report', [
            'project' => $project,
            'report' => $report,
            'issues' => $report->reportableIssues(),
            'format' => 'raw',
        ]);
    }
}
