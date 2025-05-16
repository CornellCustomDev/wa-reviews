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
        Schema::table('guidelines', function (Blueprint $table) {
            // Make the primary key, id, not auto-increment
            $table->unsignedBigInteger('id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guidelines', function (Blueprint $table) {
            // Revert the primary key, id, to auto-increment
            $table->bigIncrements('id')->change();
        });
    }
};
