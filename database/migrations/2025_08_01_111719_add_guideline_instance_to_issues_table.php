<?php

use App\Models\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->smallInteger('guideline_instance')->default(1)->after('guideline_id');
        });

        $projectGuidelineInstances = Project::with(['issues:id,project_id,guideline_id,guideline_instance'])
            ->get()
            ->pluck('issues')
            ->flatten()
            ->groupBy(function ($issue) {
                return $issue->project_id .'-'. $issue->guideline_id;
            })
            ->filter(function ($issues) {
                // Only collect groups with more than one issue
                return $issues->count() > 1;
            });

        // For each project and guideline combination, set the guideline_instance to incrementing values
        foreach ($projectGuidelineInstances as $issues) {
            $issues->each(function ($issue, $index) {
                $issue->guideline_instance = $index + 1; // Start from 1
                $issue->save();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn('guideline_instance');
        });
    }
};
