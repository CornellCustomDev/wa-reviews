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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained();
            $table->foreignId('guideline_id')->constrained();
            $table->string('assessment', 20);
            $table->string('target')->nullable();
            $table->text('description')->nullable();
            $table->string('testing_method')->nullable();
            $table->text('recommendation')->nullable();
            $table->text('image_links')->nullable();
            $table->boolean('content_issue')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
