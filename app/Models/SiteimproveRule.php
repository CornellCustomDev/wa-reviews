<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteimproveRule extends Model
{
    protected $fillable = [
        'rule_id',
        'category',
        'issues',
        'criterion_id',
    ];

    // No timestamps
    public $timestamps = false;

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(Criterion::class);
    }
}
