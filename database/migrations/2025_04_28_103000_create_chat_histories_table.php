<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_histories', function (Blueprint $table) {
            $table->ulid()->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('context_type');
            $table->unsignedBigInteger('context_id');
            $table->json('messages');
            $table->string('name')->default('New chat');
            $table->timestamps();

            $table->index(['user_id', 'context_type', 'context_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_histories');
    }
};
