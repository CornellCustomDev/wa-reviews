<?php

namespace App\Models;

use App\Enums\IssueStatus;
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
        'sia_rule_id',
        'target',
        'description',
        'recommendation',
        'status',
        'needs_mitigation',
    ];

    protected $casts = [
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
}
