<?php

namespace App\Models;

use App\Enums\GuidelineStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Scope extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'url',
        'page_content',
        'retrieved_at', // @TODO: Store this in a separate table
        'notes',
        'comments'
    ];

    protected $with = [
        'project:id,name,team_id,status',
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

    public function chats(User $user): MorphMany
    {
        return $this->morphMany(ChatHistory::class, 'context')
            ->where('user_id', $user->id);
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
