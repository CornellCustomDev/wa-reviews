<?php

use App\Enums\Agents;
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
        Schema::table('chat_histories', function (Blueprint $table) {
            $table->foreignId('agent_id')->nullable()->after('ulid')->constrained();
        });

        if (DB::table('agents')->where('name', Agents::ModelChatAgent->name)->doesntExist()) {
            DB::table('agents')->insert([
                'name' => Agents::ModelChatAgent->name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        if (DB::table('agents')->where('name', Agents::GuidelineRecommender->name)->doesntExist()) {
            DB::table('agents')->insert([
                'name' => Agents::GuidelineRecommender->name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        if (DB::table('agents')->where('name', Agents::StructuredOutput->name)->doesntExist()) {
            DB::table('agents')->insert([
                'name' => Agents::StructuredOutput->name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Set all existing chat histories to use the ModelChatAgent by default
        $agentId = DB::table('agents')->where('name', Agents::ModelChatAgent->name)->value('id');
        DB::table('chat_histories')->update(['agent_id' => $agentId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_histories', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropColumn('agent_id');
        });
    }
};
