<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScopeGuideline extends Model
{
    protected $fillable = [
        'guideline_id',
        'scope_id',
        'completed',
        'status',
    ];

    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class);
    }

    public function guideline(): BelongsTo
    {
        return $this->belongsTo(Guideline::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }
}
