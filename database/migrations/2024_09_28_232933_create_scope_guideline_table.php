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
        Schema::create('scope_guidelines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scope_id');
            $table->unsignedBigInteger('guideline_id');
            $table->boolean('completed')->default(false);
            $table->string('status', 25)->default('not-reviewed');
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
        Schema::dropIfExists('guideline_scope');
    }
};
