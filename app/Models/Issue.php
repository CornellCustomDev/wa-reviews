<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Issue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'scope_id',
        'sia_rule_id',
        'target',
        'description',
        'recommendation',
    ];

    protected $with = [
        'project:id,name,team_id,status',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class);
    }

    public function siaRule(): BelongsTo
    {
        return $this->belongsTo(SiaRule::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
