<?php

namespace App\Livewire\Forms;

use App\Models\Report;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ReportForm extends Form
{
    public ?Report $report;

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

    public function setModel(Report $report): void
    {
        $this->report = $report;
        $this->site_purpose = $report->project->site_purpose ?? '';
        $this->urls_included = $report->urls_included ?? '';
        $this->urls_excluded = $report->urls_excluded ?? '';
        $this->review_procedure = $report->review_procedure ?? '';
        $this->summary = $report->summary ?? '';
    }

    public function update(): void
    {
        $this->validate();
        $this->report->project->update(['site_purpose' => $this->site_purpose]);
        $this->report->update([
            'urls_included'    => $this->urls_included,
            'urls_excluded'    => $this->urls_excluded,
            'review_procedure' => $this->review_procedure,
            'summary'          => $this->summary,
        ]);
    }
}
