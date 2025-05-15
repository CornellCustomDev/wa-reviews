<?php

namespace App\Imports\ChecklistSpreadsheet;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMappedCells;

class SiteImport implements ToModel, WithMappedCells
{
    public function __construct(private readonly Project $project) {}

    public function mapping(): array
    {
        return [
            'name' => 'C2',
            'site_url' => 'D2',
            'assessment_date' => 'E2',
            'assessed_by' => 'I2',
            'primary_system' => 'J2',
            'notes' => 'K2',
        ];
    }

    public function model(array $row)
    {
        $this->project->name = $row['name'];
        $this->project->site_url = $row['site_url'];
        $this->project->description =
            '<p>Assessment Date: '.$row['assessment_date']."</p>\n"
            .'<p>Assessed By: '.$row['assessed_by']."</p>\n"
            .'<p>Primary Browser and OS: '.$row['primary_system']."</p>\n"
            .'<p>Other Notes/Procedure: '.$row['notes']."</p>\n";

        return $this->project;
    }
}
