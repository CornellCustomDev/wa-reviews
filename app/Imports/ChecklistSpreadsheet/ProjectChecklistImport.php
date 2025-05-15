<?php

namespace App\Imports\ChecklistSpreadsheet;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\WithConditionalSheets;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProjectChecklistImport implements WithMultipleSheets
{
    use WithConditionalSheets;

    public function __construct(private readonly Project $project) {}

    public function conditionalSheets(): array
    {
        return [
            'Scope' => new ScopesSheetImport($this->project),
            'Checklist' => new ChecklistSheetImport($this->project),
        ];
    }
}

