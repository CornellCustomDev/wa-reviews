<?php

namespace App\Models;

use Database\Factories\EvalResultFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The outcome of replaying one case, once, within one run.
 */
class EvalResult extends Model
{
    /** @use HasFactory<EvalResultFactory> */
    use HasFactory;

    protected $fillable = [
        'eval_run_id',
        'eval_case_id',
        'repeat_index',
        'output',
        'usage',
        'finish_reason',
        'latency_ms',
        'tool_calls',
        'error',
        'scores',
    ];

    protected $casts = [
        'output' => 'array',
        'usage' => 'array',
        'tool_calls' => 'array',
        'scores' => 'array',
    ];

    public function evalRun(): BelongsTo
    {
        return $this->belongsTo(EvalRun::class);
    }

    public function evalCase(): BelongsTo
    {
        return $this->belongsTo(EvalCase::class);
    }

    public function failed(): bool
    {
        return $this->error !== null;
    }

    public function score(string $metric): int|float|bool|null
    {
        return data_get($this->scores, $metric);
    }

    /**
     * @param  array<string, mixed>  $scores
     */
    public function mergeScores(array $scores): void
    {
        $this->update(['scores' => array_merge($this->scores ?? [], $scores)]);
    }
}
