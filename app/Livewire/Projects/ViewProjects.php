<?php

namespace App\Livewire\Projects;

use App\Events\ProjectChanged;
use App\Events\TeamChanged;
use App\Models\Project;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ViewProjects extends Component
{
    #[Computed]
    public function projects(): array
    {
        $teamProjects = Project::getTeamProjects(auth()->user());
        $viewerProjects = Project::getReportViewerProjects(auth()->user());

        return $teamProjects
            ->merge($viewerProjects)
            ->sortByDesc('updated_at')
            ->all();
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
