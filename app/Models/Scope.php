<?php

namespace App\Models;

use App\Enums\GuidelineStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scope extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'url',
        'page_content',
        'retrieved_at',
        'siteimprove_url',
        'notes',
        'comments'
    ];

    protected $casts = [
        'comments' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function guidelines(): HasMany
    {
        return $this->hasMany(ScopeGuideline::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(ScopeRule::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    public function pageHasBeenRetrieved(): bool
    {
        return $this->retrieved_at !== null;
    }

    public function generateScopeGuidelines(): void
    {
        $guidelinesList = Guideline::all()->pluck('id')
            ->map(function ($id) {
                return [
                    'guideline_id' => $id,
                    'completed' => false,
                    'status' => GuidelineStatus::NotStarted,
                ];
            });
        $this->guidelines()->createMany($guidelinesList);
    }

}
