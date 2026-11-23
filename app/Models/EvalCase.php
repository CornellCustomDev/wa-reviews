<?php

namespace App\Models;

use Database\Factories\EvalCaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A single replayable evaluation case mined from a historical chat history.
 */
class EvalCase extends Model
{
    /** @use HasFactory<EvalCaseFactory> */
    use HasFactory;

    protected $fillable = [
        'dataset_version',
        'agent_id',
        'source_chat_history_ulid',
        'user_id',
        'context_type',
        'context_id',
        'input',
        'baseline',
        'labels',
        'tags',
    ];

    protected $casts = [
        'input' => 'array',
        'baseline' => 'array',
        'labels' => 'array',
        'tags' => 'array',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function sourceChatHistory(): BelongsTo
    {
        return $this->belongsTo(ChatHistory::class, 'source_chat_history_ulid', 'ulid');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function context(): MorphTo
    {
        return $this->morphTo();
    }

    public function results(): HasMany
    {
        return $this->hasMany(EvalResult::class);
    }

    /**
     * Mined labels take the shape written by the extractor; gold labels are
     * curated by hand and always win where both exist.
     *
     * @return array<string, mixed>
     */
    public function effectiveLabels(): array
    {
        $labels = $this->labels ?? [];

        return array_merge($labels['mined'] ?? [], $labels['gold'] ?? []);
    }

    public function hasLabels(): bool
    {
        return $this->effectiveLabels() !== [];
    }
}
