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
            // If the image_links field already exists, change it, otherwise add it
            if (Schema::hasColumn('items', 'image_links')) {
                $table->json('image_links')->nullable()->change();
            } else {
                $table->json('image_links')->nullable()->after('recommendation');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->text('image_links')->nullable()->change();
        });
    }
};
