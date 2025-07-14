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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->index();
            $table->unsignedInteger('version')->default(1);
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['slug', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
