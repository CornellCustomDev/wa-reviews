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
        Schema::create('siteimprove_rule_guideline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siteimprove_rule_id')->constrained()->onDelete('cascade');
            $table->foreignId('guideline_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siteimprove_rule_guideline');
    }
};
