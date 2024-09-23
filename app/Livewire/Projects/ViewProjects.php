<?php

namespace App\Livewire\Projects;

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
    }

    public function render()
    {
        return view('livewire.projects.view-projects', [
            'projects' => Project::all(),
        ]);
    }
}
