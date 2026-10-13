<?php

use App\Models\EvalCase;
use App\Models\EvalRun;
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
        Schema::create('eval_results', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(EvalRun::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(EvalCase::class)->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('repeat_index')->default(0);

            /** Final text plus the parsed structure the graders read. */
            $table->json('output')->nullable();
            $table->json('usage')->nullable();
            $table->string('finish_reason', 30)->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->json('tool_calls')->nullable();
            $table->text('error')->nullable();
            /** Grader scores, metric name => value. */
            $table->json('scores')->nullable();

            $table->timestamps();

            $table->unique(['eval_run_id', 'eval_case_id', 'repeat_index'], 'eval_results_run_case_repeat_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eval_results');
    }
};
