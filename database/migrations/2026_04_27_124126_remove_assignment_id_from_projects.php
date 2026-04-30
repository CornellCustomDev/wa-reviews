<?php

use App\Models\ProjectAssignment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['assignment_id']);
            $table->dropColumn('assignment_id');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('assignment_id')
                ->nullable()
                ->after('siteimprove_id')
                ->constrained('project_assignments')
                ->cascadeOnDelete();
        });

        // Add the existing project_assignments for "reviewer" back to the projects
        $assignments = ProjectAssignment::where('role', 'reviewer')->get();
        foreach ($assignments as $assignment) {
            $project = $assignment->project;
            $project->assignment_id = $assignment->id;
            $project->saveQuietly();
        }
    }
};
