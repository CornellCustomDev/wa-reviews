<?php

use App\Models\Report;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $completedStatuses = ['review_complete', 'verification_review', 'closed'];

        $projects = DB::table('projects')->whereNull('deleted_at')->get();

        foreach ($projects as $project) {
            $isCompleted = in_array($project->status, $completedStatuses);

            $reviewer = DB::table('project_assignments')
                ->where('project_id', $project->id)
                ->where('role', 'reviewer')
                ->whereNull('deleted_at')
                ->first();

            $reportId = DB::table('reports')->insertGetId([
                'project_id'       => $project->id,
                'type'             => 'review',
                'urls_included'    => $project->urls_included,
                'urls_excluded'    => $project->urls_excluded,
                'review_procedure' => $project->review_procedure,
                'summary'          => $project->summary,
                'completed_at'     => $isCompleted ? $project->completed_at : null,
                'completed_by'     => $isCompleted && $reviewer ? $reviewer->user_id : null,
                'created_at'       => $isCompleted ? $project->completed_at : now(),
                'updated_at'       => $isCompleted ? $project->completed_at : now(),
            ]);

            if ($isCompleted) {
                $report = Report::find($reportId);
                $report->addIssuesToReport();
            }
        }
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('issues')->update(['report_id' => null]);
        DB::table('report_issues')->truncate();
        DB::table('reports')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
