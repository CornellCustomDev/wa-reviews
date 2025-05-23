<?php

namespace App\Livewire\Teams;

use App\Events\ProjectChanged;
use App\Events\TeamChanged;
use App\Models\Project;
use App\Models\Team;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ManageTeamProjects extends Component
{
    public Team $team;

    #[Computed]
    public function projects(): array
    {
        return $this->team->projects->all();
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
