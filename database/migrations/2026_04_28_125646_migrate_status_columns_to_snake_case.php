<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate projects.status display values to snake_case
        $projectMap = [
            'Not Started' => 'not_started',
            'In Progress' => 'in_progress',
            'Completed'   => 'completed',
        ];
        foreach ($projectMap as $old => $new) {
            DB::table('projects')->where('status', $old)->update(['status' => $new]);
        }

        // Update projects.status column default to snake_case
        Schema::table('projects', function (Blueprint $table) {
            $table->string('status', 25)->default('not_started')->change();
        });

        // Migrate issues.status display values to snake_case
        $issueMap = [
            'Reviewed'        => 'reviewed',
            'Fixed'           => 'fixed',
            'Not Being Fixed' => 'not_being_fixed',
            'False Positive'  => 'false_positive',
            'Verified Fixed'  => 'verified_fixed',
        ];
        foreach ($issueMap as $old => $new) {
            DB::table('issues')->where('status', $old)->update(['status' => $new]);
        }
    }

    public function down(): void
    {
        // Revert issues.status snake_case to display values
        $issueMap = [
            'reviewed'        => 'Reviewed',
            'fixed'           => 'Fixed',
            'not_being_fixed' => 'Not Being Fixed',
            'false_positive'  => 'False Positive',
            'verified_fixed'  => 'Verified Fixed',
        ];
        foreach ($issueMap as $old => $new) {
            DB::table('issues')->where('status', $old)->update(['status' => $new]);
        }

        // Revert projects.status column default
        Schema::table('projects', function (Blueprint $table) {
            $table->string('status', 25)->default('Not Started')->change();
        });

        // Revert projects.status snake_case to display values
        $projectMap = [
            'not_started' => 'Not Started',
            'in_progress' => 'In Progress',
            'completed'   => 'Completed',
        ];
        foreach ($projectMap as $old => $new) {
            DB::table('projects')->where('status', $old)->update(['status' => $new]);
        }
    }
};
