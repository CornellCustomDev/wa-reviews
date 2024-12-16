<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\Scope;
use Livewire\Component;

class SiteimprovePages extends Component
{
    public Project $project;
    public array $siteimprovePages = [];

    public function pageInScope($url): ?Scope
    {
        return $this->project->scopes->firstWhere('url', $url);
    }

    public function addToScope($page): void
    {
        // go to the route to add the page to the scope
        redirect()->route('projects.scopes.create', ['project' => $this->project, 'url' => $page['url']]);
    }
}
