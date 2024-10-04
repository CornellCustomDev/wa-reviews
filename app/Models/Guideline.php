<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guideline extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'name',
        'criterion_id',
        'category_id',
        'notes',
    ];

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(Criterion::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function actRules(): BelongsToMany
    {
        return $this->belongsToMany(ActRule::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
