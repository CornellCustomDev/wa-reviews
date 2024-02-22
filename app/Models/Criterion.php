<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
}
