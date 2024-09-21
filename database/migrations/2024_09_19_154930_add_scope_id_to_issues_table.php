<?php

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
            $table->boolean('content_issue')->nullable()->after('recommendation');
            $table->foreignId('scope_id')->nullable()->after('project_id')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropForeign(['scope_id']);
            $table->dropColumn('scope_id');
            $table->dropColumn('content_issue');
        });
    }
};
