<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Events\ProjectChanged;
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

    public function isProjectReviewer(User $user): bool
    {
        return $user->id === $this->reviewer?->id;
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
}
