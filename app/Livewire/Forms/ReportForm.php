<?php

namespace App\Livewire\Forms;

use App\Models\Project;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ReportForm extends Form
{
    public ?Project $project;

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
    #[Validate('nullable|string', as: 'URLs included')]
    public string $urls_included = '';
    #[Validate('nullable|string', as: 'URLs excluded')]
    public string $urls_excluded = '';
    #[Validate('nullable|string', as: 'Review procedure')]
    public string $review_procedure = '';
    #[Validate('nullable|string', as: 'Summary')]
    public string $summary = '';

    public function setModel(Project $project): void
    {
        $this->project = $project;
        $this->responsible_unit = $project->responsible_unit ?? '';
        $this->contact_name = $project->contact_name ?? '';
        $this->contact_netid = $project->contact_netid ?? '';
        $this->audience = $project->audience ?? '';
        $this->site_purpose = $project->site_purpose ?? '';
        $this->urls_included = $project->urls_included ?? '';
        $this->urls_excluded = $project->urls_excluded ?? '';
        $this->review_procedure = $project->review_procedure ?? '';
        $this->summary = $project->summary ?? '';
    }

    public function update(): void
    {
        $this->validate();
        $this->sanitizeFields();
        $this->project->update($this->all());
    }

    public function sanitizeFields(): void
    {
        $this->site_purpose = strip_tags($this->site_purpose);
        $this->urls_included = strip_tags($this->urls_included);
        $this->urls_excluded = strip_tags($this->urls_excluded);
        $this->review_procedure = strip_tags($this->review_procedure);
        $this->summary = strip_tags($this->summary);
    }
}
