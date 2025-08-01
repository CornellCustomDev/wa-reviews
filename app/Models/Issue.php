<?php

namespace App\Models;

use App\Enums\Agents;
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
use InvalidArgumentException;

class Issue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'scope_id',
        'target',
        'description',
        'guideline_id',
        'guideline_instance',
        'sia_rule_id',
        'assessment',
        'impact',
        'testing_method',
        'recommendation',
        'testing',
        'image_links',
        'content_issue',
        'chat_history_ulid',
        'ai_reasoning',
        'ai_status',
        'agent_id',
        'status',
        'needs_mitigation',
    ];

    protected $casts = [
        'description' => AsHtmlString::class,
        'assessment' => Assessment::class,
        'impact' => Impact::class,
        'testing_method' => TestingMethod::class,
        'recommendation' => AsHtmlString::class,
        'testing' => AsHtmlString::class,
        'image_links' => 'array',
        'ai_reasoning' => AsHtmlString::class,
        'ai_status' => AIStatus::class,
        'status' => IssueStatus::class,
        'needs_mitigation' => 'boolean',
    ];

    protected $with = [
        'project:id,name,team_id,status,siteimprove_id',
    ];

    protected static function booted()
    {
        static::creating(function (Issue $issue) {
            $issue->setGuidelineInstance($issue->guideline_instance);
        });

        static::updating(function (Issue $issue) {
            if ($issue->isDirty('guideline_id')) {
                // If the guideline ID is changed, we need to set a new instance
                $issue->setGuidelineInstance();
            } elseif ($issue->isDirty('guideline_instance')) {
                // If the guideline instance is changed, we need to validate it
                $issue->setGuidelineInstance($issue->guideline_instance);
            }
        });
    }

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
            ->where('agent_id', Agent::findAgent(Agents::ModelChatAgent)->id)
            ->where('user_id', $user->id);
    }

    public function guideline(): BelongsTo
    {
        return $this->belongsTo(Guideline::class);
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function setGuidelineInstance(?int $guideline_instance = null): void
    {
        // If no guideline ID is set, we cannot determine an instance
        if (empty($this->guideline_id)) {
            return;
        }

        $instances = $this->project->issues()->where('guideline_id', $this->guideline_id)->pluck('guideline_instance');

        // Only allow resetting the sequence if the instance is not already set
        if ($instances->contains($guideline_instance)) {
            throw new InvalidArgumentException("Guideline instance $guideline_instance already exists for this guideline.");
        }

        $lastInstance = $instances->max() ?? 0;
        $nextInstance = $lastInstance + 1;

        if ($guideline_instance > $nextInstance) {
            throw new InvalidArgumentException("Guideline instance must be sequential. Next instance is $nextInstance.");
        }

        // If no guideline instance is provided, set it to the next available instance
        if (is_null($guideline_instance)) {
            $guideline_instance = $nextInstance;
        }

        $this->guideline_instance = $guideline_instance;
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

    public function applyRecommendation(Item $item): void
    {
        $item->markAiAccepted();

        // Use the item content to update the issue
        $this->update([
            'guideline_id'   => $item->guideline_id,
            'assessment'     => $item->assessment,
            'impact'         => $item->impact,
            'testing'        => $item->testing,
            'chat_history_ulid' => $item->chat_history_ulid ?? null,
            'ai_reasoning'   => $item->ai_reasoning,
            'ai_status'      => AIStatus::Accepted,
            'recommendation' => $item->recommendation,
        ]);

        // Reject any other AI recommendations for this issue
        $this->items
            ->filter(fn (Item $item) => $item->hasUnreviewedAI())
            ->each(fn (Item $item) => $item->markAiRejected());
    }
}
