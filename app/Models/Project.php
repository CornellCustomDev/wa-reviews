<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Events\ProjectChanged;
use App\Services\SiteImprove\SiteimproveService;
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
        'assignment_id',
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
        return $this->hasOne(
            related: ProjectAssignment::class,
            foreignKey: 'project_id',
            localKey: 'id'
        )->with([
            'reviewer:id,name,email',
        ]);
    }

    public function reviewer(): HasOneThrough
    {
        return $this->throughAssignment()->hasReviewer();
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

    public static function getTeamProjects(User $user): Collection
    {
        if ($user->isAdministrator()) {
            return Project::all();
        }

        return Project::query()->whereIn('team_id', $user->teams->pluck('id'))->get();
    }

    public function assignToUser(User $user): void
    {
        // We need to first remove the existing assignment
        $this->unassign();

        // TODO: Can do this as `$project->assignment()->create(['user_id' => $user?->id]);`

        // Create an assignment
        $assignment = ProjectAssignment::create([
            'project_id' => $this->id,
            'user_id' => $user->id,
        ]);

        // Reference the assignment from the project
        $this->assignment_id = $assignment->id;
        $this->save();

        $delta = [
            'user_id'   => $user->id,
            'user_name' => $user->name,
        ];
        event(new ProjectChanged($this, 'assigned', $delta));

        // Make sure anything using this object gets the new assignment
        $this->refresh();
    }

    public function unassign(): void
    {
        $reviewer = $this->assignment?->reviewer;
        if (empty($reviewer)) {
            return;
        }

        // Deleting the assignment cascades to the project assignment, as well
        $this->assignment->delete();

        $delta = [
            'user_id'   => $reviewer->id,
            'user_name' => $reviewer->name,
        ];
        event(new ProjectChanged($this, 'unassigned', $delta));
    }

    public function isReviewer(User $user): bool
    {
        return $user->id === $this->reviewer?->id;
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

    public function addReportViewer(User $user): void
    {
        // if the user is already a viewer, do nothing
        if ($this->isReportViewer($user)) {
            return;
        }

        $this->reportViewers()->attach($user->id);

        $delta = [
            'user_id'   => $user->id,
            'user_name' => $user->name,
        ];
        event(new ProjectChanged($this, 'added viewer', $delta));
    }

    public function removeReportViewer(User $user): void
    {
        $this->reportViewers()->detach($user->id);

        $delta = [
            'user_id'   => $user->id,
            'user_name' => $user->name,
        ];
        event(new ProjectChanged($this, 'removed viewer', $delta));
    }

    public function isReportViewer(User $user): bool
    {
        return $this->reportViewers()->where('user_id', $user->id)->exists();
    }

    public static function getReportViewerProjects(User $user): Collection
    {
        return Project::query()
            ->whereHas('reportViewers', fn ($q) => $q->where('user_id', $user->id))
            ->get();
    }

    public function getReportableIssues(): Collection
    {
        return $this->issues()
            ->whereNotNull('guideline_id')
            ->with(['scope', 'guideline', 'guideline.criterion'])
            ->get()
            ->filter(fn ($issue) => $issue->isAiAccepted() || ! $issue->isAiGenerated())
            ->sort(fn ($a, $b) => $a->guideline_id <=> $b->guideline_id);
    }

    public function updateSiteimprove(): void
    {
        if (empty($this->siteimprove_url)) {
            if (!empty($this->siteimprove_id)) {
                // If we have a siteimprove_id already set but the siteimprove_url is empty, we should remove the siteimprove_id
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
            // Run to cache the siteimprove data
            SiteimproveService::make($siteimprove_id)->getPagesWithIssues(bustCache: true);
        }
    }
}
