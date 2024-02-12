<?php

namespace App\Models;

use App\Enums\Assessment;
use App\Enums\TestingMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'guideline_id',
        'assessment',
        'target',
        'description',
        'testing_method',
        'recommendation',
        'image_links',
        'content_issue',
    ];

    protected $casts = [
        'assessment' => Assessment::class,
        'testing_method' => TestingMethod::class
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}
