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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('responsible_unit')->nullable()->after('siteimprove_id');
            $table->string('contact_name')->nullable()->after('responsible_unit');
            $table->string('contact_netid', 10)->nullable()->after('contact_name');
            $table->text('audience')->nullable()->after('contact_netid');
            $table->text('site_purpose')->nullable()->after('audience');
            $table->text('urls_included')->nullable()->after('site_purpose');
            $table->text('urls_excluded')->nullable()->after('urls_included');
            $table->text('review_procedure')->nullable()->after('urls_excluded');
            $table->text('summary')->nullable()->after('review_procedure');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('responsible_unit');
            $table->dropColumn('contact_name');
            $table->dropColumn('contact_netid');
            $table->dropColumn('audience');
            $table->dropColumn('site_purpose');
            $table->dropColumn('urls_included');
            $table->dropColumn('urls_excluded');
            $table->dropColumn('review_procedure');
            $table->dropColumn('summary');
        });
    }
};
