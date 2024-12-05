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
        Schema::create('siteimprove_rules', function (Blueprint $table) {
            $table->id();
            $table->integer('rule_id');
            $table->string('category', 30);
            $table->string('issues');
            $table->foreignId('criterion_id')->nullable()->constrained();

            // rule_id + category + issues must be unique
            $table->unique(['rule_id', 'category', 'issues']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siteimprove_rules');
    }
};
