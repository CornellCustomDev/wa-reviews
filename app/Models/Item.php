<?php

namespace App\Models;

use App\Enums\Assessment;
use App\Enums\TestingMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'issue_id',
        'guideline_id',
        'assessment',
        'target',
        'description',
        'testing_method',
        'recommendation',
        'testing',
        'image_links',
        'content_issue',
    ];

    protected $casts = [
        'assessment' => Assessment::class,
        'testing_method' => TestingMethod::class
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function guideline(): BelongsTo
    {
        return $this->belongsTo(Guideline::class);
    }
}
