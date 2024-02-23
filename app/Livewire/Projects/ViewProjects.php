<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Component;

class ViewProjects extends Component
{
    public function render()
    {
        return view('livewire.projects.view-projects', [
            'projects' => Project::all(),
        ])->layout('components.layouts.app');
    }
}
