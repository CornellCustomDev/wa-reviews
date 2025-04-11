<?php

namespace App\Livewire\Projects;

use App\Enums\ProjectStatus;
use App\Models\Activity;
use App\Models\Item;
use App\Models\Project;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLog extends Component
{
    use WithPagination;

    public Project $project;
    public $perPage = 15;

    #[Computed]
    public function activities(): LengthAwarePaginator
    {
        return Activity::query()
            ->where('context_type', Project::class)
            ->where('context_id', $this->project->id)
            ->orderByDesc('id')
            ->paginate($this->perPage);
    }

    /**
     * Collect a list of the project items for linking to issues.
     */
    #[Computed]
    public function projectItems(): Collection
    {
        return $this->project->items()->with('issue:id')->select(['items.id','items.issue_id'])->get()->keyBy('id');
    }

    public function statusColor(string $status): string
    {
        return match($status) {
            ProjectStatus::NotStarted->value => 'zinc',
            ProjectStatus::InProgress->value => 'green',
            ProjectStatus::Completed->value => 'blue',
            default => 'zinc',
        };
    }

    public function subjectLink($activity): string
    {
        $name = Str::replace('App\\Models\\', '', $activity->subject_type) . ' ' . $activity->subject_id;

        $route = match ($activity->subject_type) {
            'App\Models\Issue' => route('issue.show', $activity->subject_id),
            'App\Models\Item' => $this->projectItems->has($activity->subject_id)
                ? route('issue.show', $this->projectItems->get($activity->subject_id)->issue_id)
                : null,
            default => null,
        };

        return $route ? "<a href=\"$route\">$name</a>" : $name;

    }
}
