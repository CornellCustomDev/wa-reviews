<?php

namespace App\Models;

use App\Enums\AIStatus;
use App\Enums\Assessment;
use App\Enums\Impact;
use App\Enums\IssueStatus;
use App\Enums\TestingMethod;
use App\Events\IssueChanged;
use Illuminate\Database\Eloquent\Casts\AsHtmlString;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Issue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'scope_id',
        'guideline_id',
        'sia_rule_id',
        'assessment',
        'target',
        'description',
        'testing_method',
        'recommendation',
        'testing',
        'image_links',
        'content_issue',
        'impact',
        'ai_reasoning',
        'ai_status',
        'agent_id',
        'status',
        'needs_mitigation',
    ];

    protected $casts = [
        'assessment' => Assessment::class,
        'description' => AsHtmlString::class,
        'testing_method' => TestingMethod::class,
        'recommendation' => AsHtmlString::class,
        'testing' => AsHtmlString::class,
        'image_links' => 'array',
        'impact' => Impact::class,
        'ai_reasoning' => AsHtmlString::class,
        'ai_status' => AIStatus::class,
        'status' => IssueStatus::class,
        'needs_mitigation' => 'boolean',
    ];

    protected $with = [
        'project:id,name,team_id,status,siteimprove_id',
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

    public function chats(User $user): MorphMany
    {
        return $this->morphMany(ChatHistory::class, 'context')
            ->where('user_id', $user->id);
    }

    public function guideline(): BelongsTo
    {
        return $this->belongsTo(Guideline::class);
    }

    public function isAiGenerated(): bool
    {
        return in_array($this->ai_status, [
            AIStatus::Generated,
            AIStatus::Accepted,
            AIStatus::Rejected,
        ]);
    }

    public function hasUnreviewedAi(): bool
    {
        return $this->ai_status === AIStatus::Generated;
    }

    public function wasAiGenerated(): bool
    {
        return ! empty($this->ai_status);
    }

    public function isAiAccepted(): bool
    {
        return $this->ai_status === AIStatus::Accepted;
    }

    public function markAiAccepted(): void
    {
        $this->ai_status = AIStatus::Accepted;
        $this->save();

        event(new IssueChanged($this, 'accepted', []));
    }

    /**
     * TODO: Make this happen automatically when the item is deleted?
     */
    public function markAiRejected(): void
    {
        $this->ai_status = AIStatus::Rejected;
        $this->save();

        event(new IssueChanged($this, 'rejected', []));
    }

    /**
     * TODO: Make this happen automatically when the item is updated
     */
    public function markAiModified(): void
    {
        $this->ai_status = AIStatus::Modified;
        $this->save();
    }
}
