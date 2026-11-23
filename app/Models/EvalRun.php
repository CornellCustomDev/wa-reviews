<?php

namespace App\Models;

use App\Enums\EvalRunStatus;
use Database\Factories\EvalRunFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One execution of the dataset against a specific set of models.
 */
class EvalRun extends Model
{
    /** @use HasFactory<EvalRunFactory> */
    use HasFactory;

    protected $fillable = [
        'label',
        'dataset_version',
        'is_baseline',
        'status',
        'repeats',
        'models',
        'margins',
        'git_sha',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'status' => EvalRunStatus::class,
        'is_baseline' => 'boolean',
        'models' => 'array',
        'margins' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function results(): HasMany
    {
        return $this->hasMany(EvalResult::class);
    }

    /**
     * The run every candidate against this dataset version is compared to by
     * default, so `ai:eval-report` doesn't need `--baseline` spelled out.
     */
    public static function baselineFor(string $datasetVersion): ?self
    {
        return static::query()
            ->where('dataset_version', $datasetVersion)
            ->where('is_baseline', true)
            ->latest('id')
            ->first();
    }

    /**
     * Marks this run as the current baseline for its dataset version, and
     * unmarks whichever run held that role before it. A dataset version has
     * at most one baseline at a time.
     */
    public function markAsBaseline(): void
    {
        static::query()
            ->where('dataset_version', $this->dataset_version)
            ->where('is_baseline', true)
            ->update(['is_baseline' => false]);

        $this->update(['is_baseline' => true]);
    }

    public function markRunning(): void
    {
        $this->update([
            'status' => EvalRunStatus::Running,
            'started_at' => now(),
        ]);
    }

    public function markFinished(EvalRunStatus $status = EvalRunStatus::Completed): void
    {
        $this->update([
            'status' => $status,
            'finished_at' => now(),
        ]);
    }

    /**
     * The model configured for a given chat profile on this run.
     */
    public function modelForProfile(string $profile): ?string
    {
        return data_get($this->models, "profiles.$profile");
    }
}
