<?php

namespace App\Livewire\Forms;

use App\Models\Project;
use App\Services\SiteImprove\SiteimproveService;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ProjectForm extends Form
{
    public ?Project $project;

    #[Validate('required|string|max:255', as: 'Project Name')]
    public string $name = '';
    #[Validate('required')]
    #[Validate('url', message: 'The Site URL must be a valid URL')]
    public string $site_url = '';
    #[Validate('nullable|string')]
    public string $description = '';
    #[Validate('nullable|string')]
    public string $siteimprove_url = '';
    #[Validate('nullable|string')]
    public string $siteimprove_id = '';

    public function setModel(Project $project): void
    {
        $this->project = $project;
        $this->name = $project->name;
        $this->site_url = $project->site_url;
        $this->description = $project->description;
        $this->siteimprove_url = $project->siteimprove_url ?? '';
        $this->siteimprove_id = $project->siteimprove_id ?? '';
    }

    public function store(): Project
    {
        $this->validate();

        $this->project = Project::create($this->all());
        $this->updateSiteimprove();

        return $this->project;
    }

    public function update(): void
    {
        $this->validate();

        $this->project->update($this->all());
        $this->updateSiteimprove();
    }

    protected function updateSiteimprove()
    {
        $siteimprove_id = $this->project->siteimprove_id ?: (SiteimproveService::findSite($this->project->site_url) ?? '');
        if ($siteimprove_id) {
            if (empty($this->project->siteimprove_id)) {
                $this->project->update([
                    'siteimprove_id' => $siteimprove_id,
                ]);
            }
            SiteimproveService::make($siteimprove_id)->getPagesWithIssues(bustCache: true);
        }
    }
}
