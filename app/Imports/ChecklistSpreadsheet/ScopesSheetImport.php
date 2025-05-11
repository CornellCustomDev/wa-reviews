<?php

namespace App\Imports\ChecklistSpreadsheet;

use App\Models\Project;
use App\Models\Scope;;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ScopesSheetImport implements ToModel, WithStartRow
{
    public function __construct(private readonly Project $project) {}

    public function model(array $row)
    {
        if (empty($row[1])) {
            return null; // Skip empty rows
        }

        return new Scope([
            'project_id' => $this->project->id,
            'title' => $row[1],
            'url' => rtrim(trim($row[3] ?? ''), '/') ?: null,
            'notes' => $row[4] ?? null,
        ]);
    }

    public function startRow(): int
    {
        return 5; // Start reading from row 5
    }
}

