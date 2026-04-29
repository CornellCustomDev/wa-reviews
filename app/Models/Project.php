<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Events\ProjectChanged;
use App\Services\SiteImprove\SiteimproveService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'site_url',
        'description',
        'siteimprove_url',
        'siteimprove_id',
        'status',
        'completed_at',
        'responsible_unit',
        'contact_name',
        'contact_netid',
        'audience',
        'site_purpose',
        'urls_included',
        'urls_excluded',
        'review_procedure',
        'summary',
    ];

    protected $casts = [
        'status' => ProjectStatus::class,
        'completed_at' => 'datetime',
    ];

    protected $with = [
        'team:id,name',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function assignment(): HasOne
    {
        return $this->hasOne(ProjectAssignment::class, 'project_id')
            ->where('role', 'reviewer')
            ->with(['reviewer:id,name,email']);
    }

    public function verifierAssignment(): HasOne
    {
        return $this->hasOne(ProjectAssignment::class, 'project_id')
            ->where('role', 'verifier')
            ->with(['reviewer:id,name,email']);
    }

    public function reviewer(): HasOneThrough
    {
        return $this->throughAssignment()->hasReviewer()
            ->where('project_assignments.role', 'reviewer');
    }

    public function verifier(): HasOneThrough
    {
        return $this->throughVerifierAssignment()->hasReviewer()
            ->where('project_assignments.role', 'verifier');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ProjectAssignment::class, 'project_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    public function scopes(): HasMany
    {
        return $this->hasMany(Scope::class);
    }

    public function items(): HasManyThrough
    {
        return $this->hasManyThrough(Item::class, Issue::class);
    }

    public function reportViewers(): BelongsToMany
    {
        return $this->belongsToMany(
            related: User::class,
            table: 'project_viewers',
            foreignPivotKey: 'project_id',
            relatedPivotKey: 'user_id',
            parentKey: 'id',
            relatedKey: 'id'
        );
    }

    public function assignToUser(User $user): void
    {
        $this->unassign();

        ProjectAssignment::create([
            'project_id' => $this->id,
            'user_id' => $user->id,
            'role' => 'reviewer',
        ]);

        $delta = [
            'user_id' => $user->id,
            'user_name' => $user->name,
        ];
        event(new ProjectChanged($this, 'assigned', $delta));

        $this->refresh();
    }

    public function unassign(): void
    {
        $reviewer = $this->assignment?->reviewer;
        if (empty($reviewer)) {
            return;
        }

        $this->assignment->delete();

        $delta = [
            'user_id' => $reviewer->id,
            'user_name' => $reviewer->name,
        ];
        event(new ProjectChanged($this, 'unassigned', $delta));
    }

    public function assignVerifier(User $user): void
    {
        $this->unassignVerifier();

        ProjectAssignment::create([
            'project_id' => $this->id,
            'user_id' => $user->id,
            'role' => 'verifier',
        ]);

        $delta = [
            'user_id' => $user->id,
            'user_name' => $user->name,
        ];
        event(new ProjectChanged($this, 'verifier assigned', $delta));

        $this->refresh();
    }

    public function unassignVerifier(): void
    {
        $verifier = $this->verifierAssignment?->reviewer;
        if (empty($verifier)) {
            return;
        }

        $this->verifierAssignment->delete();

        $delta = [
            'user_id' => $verifier->id,
            'user_name' => $verifier->name,
        ];
        event(new ProjectChanged($this, 'verifier unassigned', $delta));
    }

    public function isReviewer(User $user): bool
    {
        return $user->id === $this->reviewer?->id;
    }

    public function isVerifier(User $user): bool
    {
        return $user->id === $this->verifier?->id;
    }

    public function isNotStarted(): bool
    {
        return $this->status->isNotStarted();
    }

    public function isInProgress(): bool
    {
        return $this->status->isInProgress();
    }

    public function isCompleted(): bool
    {
        return $this->status->isCompleted();
    }

    public function isPostReview(): bool
    {
        return $this->status->isPostReview();
    }

    public function isReviewComplete(): bool
    {
        return $this->status->isReviewComplete();
    }

    public function isClosed(): bool
    {
        return $this->status->isClosed();
    }

    public function addReportViewer(User $user): void
    {
        if ($this->isReportViewer($user)) {
            return;
        }

        $this->reportViewers()->attach($user->id);

        $delta = [
            'user_id' => $user->id,
            'user_name' => $user->name,
        ];
        event(new ProjectChanged($this, 'added viewer', $delta));
    }

    public function removeReportViewer(User $user): void
    {
        $this->reportViewers()->detach($user->id);

        $delta = [
            'user_id' => $user->id,
            'user_name' => $user->name,
        ];
        event(new ProjectChanged($this, 'removed viewer', $delta));
    }

    public function isReportViewer(User $user): bool
    {
        return $this->reportViewers()->where('user_id', $user->id)->exists();
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function reportViewerProjects(Builder $query, User $user): void
    {
        $query->whereHas('reportViewers', fn ($q) => $q->where('user_id', $user->id));
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        if ($user->isAdministrator()) {
            return;
        }

        $query->where(function (Builder $query) use ($user) {
            $query->whereIn('team_id', $user->teams->pluck('id'));
            $query->orWhereHas('reportViewers', fn ($q) => $q->where('user_id', $user->id));
        });
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function withReviewer(Builder $query): void
    {
        $query
            ->leftJoin('project_assignments as reviewer_pa', function ($join) {
                $join->on('reviewer_pa.project_id', '=', 'projects.id')
                    ->where('reviewer_pa.role', '=', 'reviewer')
                    ->whereNull('reviewer_pa.deleted_at');
            })
            ->leftJoin('users as reviewer', 'reviewer_pa.user_id', '=', 'reviewer.id');
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function withVerifier(Builder $query): void
    {
        $query
            ->leftJoin('project_assignments as verifier_pa', function ($join) {
                $join->on('verifier_pa.project_id', '=', 'projects.id')
                    ->where('verifier_pa.role', '=', 'verifier')
                    ->whereNull('verifier_pa.deleted_at');
            })
            ->leftJoin('users as verifier', 'verifier_pa.user_id', '=', 'verifier.id');
    }

    public static function activeProjects(User $user): Builder
    {
        return static::query()->visibleTo($user)
            ->whereIn('status', ProjectStatus::activeCases())
            ->withReviewer()
            ->select('projects.*');
    }

    public static function myProjects(User $user): Builder
    {
        return static::query()
            ->where(function (Builder $query) use ($user) {
                $query->whereHas('assignments', fn ($q) => $q->where('user_id', $user->id)->whereNull('deleted_at'));
                $query->orWhereHas('reportViewers', fn ($q) => $q->where('user_id', $user->id));
            })
            ->withReviewer()
            ->withVerifier()
            ->select('projects.*');
    }

    public static function reviewedProjects(User $user): Builder
    {
        return static::query()->visibleTo($user)
            ->whereIn('status', ProjectStatus::reviewedCases())
            ->withReviewer()
            ->withVerifier()
            ->select('projects.*');
    }

    public static function completedProjects(User $user): Builder
    {
        return static::query()->visibleTo($user)
            ->whereIn('status', ProjectStatus::completedCases())
            ->withReviewer()
            ->withVerifier()
            ->select('projects.*');
    }

    public function getReportableIssues(): Collection
    {
        return $this->issues()
            ->whereNotNull('guideline_id')
            ->with(['scope', 'guideline:id,number,name,criterion_id', 'guideline.criterion:id,number,name,level'])
            ->get()
            ->filter(fn ($issue) => $issue->isAiAccepted() || ! $issue->isAiGenerated())
            ->sort(fn ($a, $b) => $a->guideline_id <=> $b->guideline_id);
    }

    public function updateSiteimprove(): void
    {
        if (empty($this->siteimprove_url)) {
            if (! empty($this->siteimprove_id)) {
                $this->update([
                    'siteimprove_id' => null,
                ]);
            }

            return;
        }
        $siteimprove_id = $this->siteimprove_id ?: (SiteimproveService::findSite($this->site_url) ?? '');
        if ($siteimprove_id) {
            if (empty($this->siteimprove_id)) {
                $this->update([
                    'siteimprove_id' => $siteimprove_id,
                ]);
            }
            SiteimproveService::make($siteimprove_id)->getPagesWithIssues(bustCache: true);
        }
    }
}
