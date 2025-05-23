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
            // client marks addressed, can be marked verified, should be an enumeration
            $table->string('status', 20)->nullable()->after('content_issue');
            // barrier mitigation required, just a boolean
            $table->boolean('needs_mitigation')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('needs_mitigation');
        });
    }
};
