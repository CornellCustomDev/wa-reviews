<?php

use App\Models\Report;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $completedStatuses = ['review_complete', 'verification_review', 'closed'];

        $projects = DB::table('projects')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->lazyById();

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
                // Keep this null for now so we can attach issues before the report is considered completed.
                'completed_at'     => null,
                'completed_by'     => null,
                'created_at'       => $project->created_at,
                'updated_at'       => $project->created_at,
            ]);

            $report = Report::findOrFail($reportId);

            if ($isCompleted) {
                $report->addIssuesToReport();

                DB::table('reports')->where('id', $reportId)->update([
                    'completed_at' => $project->completed_at,
                    'completed_by' => $reviewer?->user_id,
                    'updated_at'   => $project->completed_at,
                ]);
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
