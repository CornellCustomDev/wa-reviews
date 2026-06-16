<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Report;

class ReportRawController extends Controller
{
    public function __invoke(Report $report)
    {
        return view('exports.project-report', [
            'project' => $report->project,
            'report' => $report,
            'issues' => $report->reportableIssues(),
            'format' => 'raw',
        ]);
    }
}
