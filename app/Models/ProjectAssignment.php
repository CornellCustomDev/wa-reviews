<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectAssignment extends Model
{
    use HasTimestamps;
    use SoftDeletes;

    protected $fillable = ['project_id', 'user_id', 'role'];

    public static function boot(): void
    {
        parent::boot();

        static::saving(function (self $assignment) {
            if (ProjectAssignment::where('project_id', $assignment->project_id)
                ->where('role', $assignment->role)
                ->whereNull('deleted_at')
                ->exists()) {
                throw new \Exception("Duplicate entry for project_id={$assignment->project_id} role={$assignment->role}.");
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function reviewer(): HasOne
    {
        return $this->hasOne(
            related: User::class,
            foreignKey: 'id',
            localKey: 'user_id'
        );
    }
}
