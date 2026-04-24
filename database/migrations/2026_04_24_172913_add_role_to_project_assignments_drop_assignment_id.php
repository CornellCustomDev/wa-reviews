<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_assignments', function (Blueprint $table) {
            $table->enum('role', ['reviewer', 'verifier'])->default('reviewer')->after('user_id');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['assignment_id']);
            $table->dropColumn('assignment_id');
        });
    }

    public function down(): void
    {
        Schema::table('project_assignments', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('assignment_id')
                ->nullable()
                ->after('siteimprove_id')
                ->constrained('project_assignments')
                ->cascadeOnDelete();
        });
    }
};
