<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function siteimproveRules(): HasMany
    {
        return $this->hasMany(SiteimproveRule::class, 'rule_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }
}
