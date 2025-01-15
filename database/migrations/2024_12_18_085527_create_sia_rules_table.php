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
        Schema::create('sia_rules', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('alfa', 8);
            $table->string('name', 255)->nullable();
            $table->string('name_html', 500)->nullable();
            $table->string('act_rule_id', 7)->nullable()->index();
            $table->longText('rule_html')->nullable();
            $table->timestamps();

            $table->foreign('act_rule_id')->references('id')->on('act_rules')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sia_rules');
    }
};
