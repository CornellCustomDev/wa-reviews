<?php

namespace App\Livewire\Projects;

use App\Events\ProjectChanged;
use App\Events\TeamChanged;
use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ViewProjects extends Component
{
    public function delete(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();

        event(new TeamChanged($project->team, $project, 'deleted', [
            'project name' => $project->name,
            'site url' => $project->site_url,
        ]));
        event(new ProjectChanged($project, 'deleted', []));
    }

    public function render()
    {
        return view('livewire.projects.view-projects', [
            'projects' => Project::getTeamProjects(auth()->user()),
        ]);
    }
}
