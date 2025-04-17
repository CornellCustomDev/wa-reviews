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
        Schema::table('items', function (Blueprint $table) {
            $table->string('ai_status')->nullable()->after('impact');

            $table->foreignId('agent_id')->nullable()->after('ai_status')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('ai_status');

            $table->dropForeign(['agent_id']);
            $table->dropColumn('agent_id');
        });
    }
};
