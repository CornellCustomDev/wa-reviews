<?php

namespace App\Livewire\Projects;

use App\Enums\ProjectStatus;
use App\Models\Activity;
use App\Models\Item;
use App\Models\Project;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLog extends Component
{
    use WithPagination;

    public Project $project;

    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 15;

    public function sort($column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function activities(): LengthAwarePaginator
    {
        return Activity::query()
            ->where('project_id', $this->project->id)
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate($this->perPage);
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
            'App\Models\Item' => route('issue.show', Item::find($activity->subject_id)->issue_id),
            default => null,
        };

        return $route ? "<a href=\"$route\">$name</a>" : $name;

    }
}
