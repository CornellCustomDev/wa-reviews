<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Criterion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'number',
        'level',
    ];

    public function guidelines(): HasMany
    {
        return $this->hasMany(Guideline::class);
    }

    public function actRules(): BelongsToMany
    {
        return $this->belongsToMany(ActRule::class, 'act_rule_criterion');
    }

    public function getNumberName(): string
    {
        return "$this->number $this->name";
    }

    public function getLongName(): string
    {
        return "$this->number $this->name ($this->level)";
    }
}
