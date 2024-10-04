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
        Schema::table('scopes', function (Blueprint $table) {
            $table->longText('page_content')->nullable()->after('url');
            $table->timestamp('retrieved_at')->nullable()->after('page_content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scopes', function (Blueprint $table) {
            $table->dropColumn('page_content');
            $table->dropColumn('retrieved_at');
        });
    }
};
