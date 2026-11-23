<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One pass of a dataset through one set of models.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('eval_runs', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('dataset_version', 50);
            /** The run every candidate against this dataset version is compared to by default. At most one per version. */
            $table->boolean('is_baseline')->default(false);
            $table->string('status', 20)->default('pending');
            $table->unsignedSmallInteger('repeats')->default(1);
            /** Model per chat profile, plus provider, at the time of the run. */
            $table->json('models');
            /** Non-inferiority margins, registered before results are seen. */
            $table->json('margins')->nullable();
            /** The code the run executed under; prompt changes move results as much as models do. */
            $table->string('git_sha', 40)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['dataset_version', 'is_baseline']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eval_runs');
    }
};
