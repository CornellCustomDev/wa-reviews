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
        Schema::create('sia_rule_criterion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sia_rule_id')->constrained('sia_rules');
            $table->foreignId('criterion_id')->constrained('criteria');
            $table->string('level', 25);
            $table->string('criterion', 10);
            $table->string('name');
            $table->string('link');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sia_rule_criterion');
    }
};
