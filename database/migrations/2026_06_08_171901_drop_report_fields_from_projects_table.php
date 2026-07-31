<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['urls_included', 'urls_excluded', 'review_procedure', 'summary']);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->text('urls_included')->nullable()->after('site_purpose');
            $table->text('urls_excluded')->nullable()->after('urls_included');
            $table->text('review_procedure')->nullable()->after('urls_excluded');
            $table->text('summary')->nullable()->after('review_procedure');
        });
    }
};
