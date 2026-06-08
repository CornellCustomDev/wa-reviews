<?php

namespace App\Livewire\Projects;

use App\Livewire\Forms\ReportForm;
use App\Models\Report;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class UpdateReport extends Component
{
    public ReportForm $form;

    public function mount(Report $report)
    {
        $this->form->setModel($report);
    }

    public function save()
    {
        $this->authorize('update', $this->form->report);
        $this->form->update();

        $this->dispatch('close-edit');
        $this->dispatch('report-updated');
    }
}
