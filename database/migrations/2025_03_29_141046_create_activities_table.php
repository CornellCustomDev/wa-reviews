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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();

            // Actor: the user or agent who performed the action.
            $table->string('actor_type');
            $table->unsignedBigInteger('actor_id');

            // Context: polymorphic reference to a Project, Team, etc.
            $table->string('context_type');
            $table->unsignedBigInteger('context_id');

            // Subject: polymorphic reference to an Issue, Item, etc.
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');

            // Describes the action taken (e.g., 'create', 'update', 'approve')
            $table->string('action', 50);

            // Stores the JSON delta of changes (optional)
            $table->json('delta')->nullable();

            // Timestamps (created_at captures when the activity occurred)
            $table->timestamps();

            // Indexes for efficient querying on polymorphic relationships and timestamps.
            $table->index(['actor_id', 'actor_type']);
            $table->index(['context_id', 'context_type']);
            $table->index(['subject_id', 'subject_type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
