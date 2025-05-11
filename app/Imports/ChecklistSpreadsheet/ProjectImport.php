<?php

namespace App\Imports\ChecklistSpreadsheet;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProjectImport implements WithMultipleSheets
{
    public function __construct(private readonly Project $project) {}

    public function sheets(): array
    {
        return [
            'Checklist' => new SiteImport($this->project),
            'Scope' => new SiteimproveImport($this->project),
        ];
    }

}
