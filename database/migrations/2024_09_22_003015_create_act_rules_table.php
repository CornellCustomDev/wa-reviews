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
        Schema::create('act_rules', function (Blueprint $table) {
            $table->string('id', 7)->primary();
            $table->string('filename');
            $table->string('name');
            $table->json('metadata');
            $table->text('applicability');
            $table->text('expectation');
            $table->text('assumptions');
            $table->text('accessibility_support');
            $table->text('background');
            $table->text('test_cases');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('act_rules');
    }
};
