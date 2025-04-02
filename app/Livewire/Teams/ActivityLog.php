<?php

namespace App\Livewire\Teams;

use App\Models\Activity;
use App\Models\Team;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLog extends Component
{
    use WithPagination;

    public Team $team;

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
            ->where('context_type', Team::class)
            ->where('context_id', $this->team->id)
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate($this->perPage);
    }

    public function subjectLink($activity): string
    {
        $name = match ($activity->subject_type) {
            'App\Models\User' => User::find($activity->subject_id)?->name,
            default => Str::replace('App\\Models\\', '', $activity->subject_type) . ' ' . $activity->subject_id,
        };

        $route = match ($activity->subject_type) {
            'App\Models\Team' => route('team.show', $activity->subject_id),
            'App\Models\Project' => route('project.show', $activity->subject_id),
            default => null,
        };

        return $route ? "<a href=\"$route\">$name</a>" : $name;

    }

    #[On('team-changes')]
    public function teamChanges(): void
    {
        unset($this->activities);
    }
}
