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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scope_id')->constrained()->cascadeOnDelete();
            $table->string('url')->nullable();
            $table->longText('page_content')->nullable();
            $table->timestamp('retrieved_at')->nullable();
            $table->string('siteimprove_page_id')->nullable();
            $table->string('siteimprove_report_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
