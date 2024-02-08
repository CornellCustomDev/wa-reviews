<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'target',
        'description',
        'recommendation',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
