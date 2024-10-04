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
        Schema::create('scope_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scope_id');
            $table->unsignedBigInteger('guideline_id');
            $table->string('rule_class');
            $table->string('rule_name');
            $table->text('css_selector');
            $table->text('description');

            $table->string('ai_assessment', 20)->nullable();
            $table->string('ai_description')->nullable();
            $table->text('ai_reasoning')->nullable();
            $table->timestamps();

            $table->foreign('scope_id')->references('id')->on('scopes')->onDelete('cascade');
            $table->foreign('guideline_id')->references('id')->on('guidelines')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scope_rules');
    }
};
