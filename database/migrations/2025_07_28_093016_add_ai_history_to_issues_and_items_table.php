<?php

use App\Models\ChatHistory;
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
        Schema::table('issues', function (Blueprint $table) {
            $table->foreignIdFor(ChatHistory::class, 'chat_history_ulid')
                ->nullable()
                ->after('impact')
                ->constrained('chat_histories', 'ulid');
        });
        Schema::table('items', function (Blueprint $table) {
            $table->foreignIdFor(ChatHistory::class, 'chat_history_ulid')
                ->nullable()
                ->after('impact')
                ->constrained('chat_histories', 'ulid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropForeign(['chat_history_ulid']);
            $table->dropColumn('chat_history_ulid');
        });
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['chat_history_ulid']);
            $table->dropColumn('chat_history_ulid');
        });
    }
};
