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

        // Add Agents::GuidelineRecommender to the agents table if it doesn't exist
        if (DB::table('agents')->where('name', Agents::GuidelineRecommender->name)->doesntExist()) {
            DB::table('agents')->insert([
                'name' => Agents::GuidelineRecommender->name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
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
