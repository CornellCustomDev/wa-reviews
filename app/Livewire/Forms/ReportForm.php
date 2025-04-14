<?php

namespace App\Livewire\Forms;

use App\Models\Project;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ReportForm extends Form
{
    public ?Project $project;

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
        $this->urls_included = $project->urls_included ?? '';
        $this->urls_excluded = $project->urls_excluded ?? '';
        $this->review_procedure = $project->review_procedure ?? '';
        $this->summary = $project->summary ?? '';
    }

    public function update(): void
    {
        $this->validate();
        $this->project->update($this->all());
    }
}
