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
        Schema::create('act_rule_criterion', function (Blueprint $table) {
            $table->id();
            $table->string('act_rule_id', 7);
            $table->unsignedBigInteger('criterion_id');

            $table->foreign('act_rule_id')->references('id')->on('act_rules')->onDelete('cascade');
            $table->foreign('criterion_id')->references('id')->on('criteria')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('act_rule_criterion');
    }
};
