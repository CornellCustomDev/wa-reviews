<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SiaRule extends Model
{
    protected $guarded = [];

    // No timestamps, no autoincrement id
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    public function actRule(): BelongsTo
    {
        return $this->belongsTo(ActRule::class);
    }

    public function criteria(): BelongsToMany
    {
        return $this->belongsToMany(Criterion::class, 'sia_rule_criterion')
            ->as('requirements')
            ->withPivot(['level', 'criterion', 'name', 'link'])
            ->withTimestamps();
    }
}
