<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Component;

class ShowProject extends Component
{
    public Project $project;

    public function render()
    {
        $this->authorize('view', $this->project);
        return view('livewire.projects.show-project')
            ->layout('components.layouts.app', [
                'breadcrumbs' => $this->getBreadcrumbs(),
            ]);
    }

    protected function getBreadcrumbs(): array
    {
        return [
            'Projects' => route('projects'),
            $this->project->name => 'active',
        ];
    }

}
