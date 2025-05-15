<?php

namespace App\Livewire\Projects;

use App\Imports\ChecklistSpreadsheet\ProjectImport;
use App\Models\Project;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ChecklistSpreadsheet\ProjectChecklistImport;

class UploadProjectData extends Component
{
    use WithFileUploads;

    public Project $project;

    public $checklist;

    public function save(): void
    {
        $this->authorize('manage-project', $this->project);

        $this->validate([
            'checklist' => 'required|file|mimes:xlsx',
        ]);

        /** @var TemporaryUploadedFile $file */
        $file = $this->checklist;
        // $storedFile = $file->storeAs("checklists/{$this->project->id}", $file->getClientOriginalName(), 'public');

        $this->importProject($file);
        $this->importScopes($file);
        $this->importChecklist($file);

        $this->modal('upload-project-data')->close();
        $this->dispatch('refresh-project');
    }

    private function importProject($file): void
    {
        $import = new ProjectImport($this->project);
        Excel::import($import, $file);
    }

    private function importScopes($file): void
    {
        $import = new ProjectChecklistImport($this->project);
        $import->onlySheets('Scope');
        Excel::import($import, $file);
    }

    private function importChecklist($file): void
    {
        $import = new ProjectChecklistImport($this->project);
        $import->onlySheets('Checklist');
        Excel::import($import, $file);
    }
}
