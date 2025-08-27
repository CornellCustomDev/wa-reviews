<?php

namespace App\Livewire\Projects;

use App\Events\ProjectChanged;
use App\Events\TeamChanged;
use App\Livewire\Support\WithSortedPagination;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ViewProjects extends Component
{
    use WithSortedPagination;

    #[Url]
    public string $tab = 'active';

    #[Computed]
    public function activeProjects(): LengthAwarePaginator
    {
        $pageName = 'active-page';
        $this->setSortDefaults($pageName, 'created_at', 'desc');
        $query = Project::activeProjects(auth()->user())
            ->with(['reviewer']);

        return $this->sortQuery($query, $pageName)
            ->paginate(10, pageName: $pageName);
    }

    #[Computed]
    public function myProjects(): LengthAwarePaginator
    {
        $pageName = 'my-page';
        $this->setSortDefaults($pageName, 'created_at', 'desc');
        $query = Project::myProjects(auth()->user())
            ->with(['reviewer']);

        return $this->sortQuery($query, $pageName)
            ->paginate(10, pageName: $pageName);
    }

    #[Computed]
    public function completedProjects(): LengthAwarePaginator
    {
        $pageName = 'completed-page';
        $this->setSortDefaults($pageName, 'created_at', 'desc');
        $query = Project::completedProjects(auth()->user())
            ->with(['reviewer']);

        return $this->sortQuery($query, $pageName)
            ->paginate(10, pageName: $pageName);
    }

    #[Computed]
    public function showTeams(): bool
    {
        return auth()->user()->isAdministrator() || auth()->user()->teams->count() > 1;
    }

    #[Computed(persist: true)]
    public function getTeamsWithCreateProjectPermission(): Collection
    {
        $user = auth()->user();
        return Team::get()
            ->filter(fn(Team $team) => $user->can('create-projects', $team))
            ->sortBy('name');
    }

    public function delete(Project $project): void
    {
        $this->authorize('delete', $project);
        $project->delete();

        event(new TeamChanged($project->team, $project, 'deleted', [
            'project name' => $project->name,
            'site url' => $project->site_url,
        ]));
        event(new ProjectChanged($project, 'deleted', []));

        unset($this->projects);
    }
}
