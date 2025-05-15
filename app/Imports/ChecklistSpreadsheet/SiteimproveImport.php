<?php

namespace App\Imports\ChecklistSpreadsheet;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;

class SiteimproveImport implements OnEachRow
{
    public function __construct(private readonly Project $project) {}

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        if ($rowIndex !== 1) {
            return; // Skip all rows except the first one
        }

        $row = $row->toArray();
        if (!empty($row[3]) && $row[3] !== $this->project->siteimprove_url) {
            $this->project->siteimprove_url = $row[3];
            $this->project->save();
            $this->project->updateSiteimprove();
        }
    }
}
