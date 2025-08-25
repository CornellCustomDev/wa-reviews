<?php

namespace App\Livewire\Forms;

use App\Events\ProjectChanged;
use App\Events\TeamChanged;
use App\Models\Project;
use App\Models\Team;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ProjectForm extends Form
{
    public ?Project $project;

    #[Validate('nullable')]
    public $team_id = '';
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
    #[Validate('nullable|string|max:255', as: 'Responsible unit at Cornell')]
    public string $responsible_unit = '';
    #[Validate('nullable|string|max:255', as: 'Name')]
    public string $contact_name = '';
    #[Validate('nullable|string|max:10', as: 'NetID')]
    public string $contact_netid = '';
    #[Validate('nullable|string', as: 'Audience')]
    public string $audience = '';
    #[Validate('nullable|string', as: 'Site purpose')]
    public string $site_purpose = '';

    public function setModel(Project $project): void
    {
        $this->project = $project;
        $this->team_id = $project->team_id ?? '';
        $this->name = $project->name;
        $this->site_url = $project->site_url;
        $this->description = $project->description;
        $this->siteimprove_url = $project->siteimprove_url ?? '';
        $this->siteimprove_id = $project->siteimprove_id ?? '';
        $this->responsible_unit = $project->responsible_unit ?? '';
        $this->contact_name = $project->contact_name ?? '';
        $this->contact_netid = $project->contact_netid ?? '';
        $this->audience = $project->audience ?? '';
        $this->site_purpose = $project->site_purpose ?? '';
    }

    public function getTeamSelectArray(): array
    {
        // Get the teams that the user is on and can create projects in
        return auth()->user()->getTeams()
            ->filter(fn (Team $team) => auth()->user()->can('create-projects', $team))
            ->map(fn ($team) => [
                'value' => $team->id,
                'option' => $team->name,
            ])
            ->toArray();
    }

    public function store(Team $team): Project
    {
        $this->validate();

        $this->project = $team->projects()->create($this->all());

        event(new ProjectChanged($this->project, 'created'));
        event(new TeamChanged($this->project->team, $this->project, 'created'));

        $this->project->updateSiteimprove();

        return $this->project;
    }

    public function update(): void
    {
        $this->validate();

        $attributes = $this->all();
        $this->project->update($attributes);

        event(new ProjectChanged($this->project, 'updated'));

        $newTeamId = $this->project->getChanges()['team_id'] ?? null;
        if ($newTeamId) {
            $delta = [
                'project name' => $this->project->name,
                'site url' => $this->project->site_url,
            ];
            event(new TeamChanged($this->project->team, $this->project, 'removed', $delta));
            event(new TeamChanged(Team::find($newTeamId), $this->project, 'added', $delta));
        }

        $this->project->updateSiteimprove();
    }
}
