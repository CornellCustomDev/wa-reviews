<?php

use App\Models\Agent;
use App\Models\ChatHistory;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One replayable evaluation case, mined from a historical chat history.
 *
 * A history row can seed a case for more than one agent: a recommender
 * transcript is also the input to a StructuredOutput case, because that is
 * exactly how the two agents chain in production. Hence the unique key
 * includes the agent.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('eval_cases', function (Blueprint $table) {
            $table->id();
            $table->string('dataset_version', 50);
            $table->foreignIdFor(Agent::class)->constrained();
            $table->foreignIdFor(ChatHistory::class, 'source_chat_history_ulid')
                ->nullable()
                ->constrained('chat_histories', 'ulid')
                ->nullOnDelete();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();
            $table->string('context_type')->nullable();
            $table->unsignedBigInteger('context_id')->nullable();

            /** Replay input: prompt text, schema, and anything else the runner needs. */
            $table->json('input');
            /** Historical output, usage and finish reason. Reference and drift only. */
            $table->json('baseline');
            /** Human labels, keyed by source: {"mined": {...}, "gold": {...}}. Gold wins where both exist. */
            $table->json('labels')->nullable();
            /** Stratification tags used for sampling and per-stratum reporting. */
            $table->json('tags')->nullable();

            $table->timestamps();

            $table->unique(
                ['dataset_version', 'agent_id', 'source_chat_history_ulid'],
                'eval_cases_version_agent_source_unique'
            );
            $table->index(['dataset_version', 'agent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eval_cases');
    }
};
