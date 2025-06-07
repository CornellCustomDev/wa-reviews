<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('scopes', function (Blueprint $table) {
            $table->foreignId('current_page_id')->nullable()->after('url')->constrained('pages')->nullOnDelete();
        });

        // Migrate existing data from scopes table to pages table
        $scopes = DB::table('scopes')->whereNotNull('page_content')->orWhereNotNull('retrieved_at')->get();

        foreach ($scopes as $scope) {
            DB::table('pages')->insert([
                'scope_id' => $scope->id,
                'url' => $scope->url,
                'page_content' => $scope->page_content,
                'retrieved_at' => $scope->retrieved_at,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $pageId = DB::getPdo()->lastInsertId();
            DB::table('scopes')
                ->where('id', $scope->id)
                ->update(['current_page_id' => $pageId]);
        }

        Schema::table('scopes', function (Blueprint $table) {
            $table->dropColumn('page_content');
            $table->dropColumn('retrieved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scopes', function (Blueprint $table) {
            $table->longText('page_content')->nullable();
            $table->timestamp('retrieved_at')->nullable();
        });

        // Repopulate data from pages table, assume no data has been added, so 1-to-1 data
        $pages = DB::table('pages')->get();
        foreach ($pages as $page) {
            DB::table('scopes')
                ->where('id', $page->scope_id)
                ->update([
                    'page_content' => $page->page_content,
                    'retrieved_at' => $page->retrieved_at
                ]);
        }

        Schema::table('scopes', function (Blueprint $table) {
            $table->dropForeign(['current_page_id']);
            $table->dropColumn('current_page_id');
        });
    }
};
