<?php

namespace App\Imports\ChecklistSpreadsheet;

use App\Enums\Assessment;
use App\Models\Issue;
use App\Models\Item;
use App\Models\Project;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\PersistRelations;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ChecklistSheetImport implements ToModel, PersistRelations, WithStartRow
{
    private Collection $scopes;

    public function __construct(private readonly Project $project)
    {
        $this->scopes = $this->project->scopes()->select('id', 'url')->get();
    }

    public function model(array $row): ?Item
    {
        if (empty($row[0])) {
            return null; // Skip empty rows
        }
        if ($row[0] === 'UX') {
            // TODO: Handle UX rows
            return null; // Skip UX rows
        }
        if (empty($row[8])) {
            return null; // Skip rows without a target
        }
        if ($row[4] || $row[7]) {
            return null; // Skip rows with pass or not applicable
        }

        $guideline_number = intval($row[0]);
        $assessment = $row[5] ? Assessment::Warn : Assessment::Fail;
        $target = $row[8];
        $description = $row[9];
        $testing = $row[10];
        $recommendation = $row[11];
        $image_links = $row[12];
        $content_issue = $row[13];

        // Adapted from https://daringfireball.net/2010/07/improved_regex_for_matching_urls
        $pattern = '/(?i)\b('
            // Protocol + domain things
            . '(?:[a-z][\w-]+:(?:\/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,63}\/)'
            // Anything after the slashes
            . '(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+'
            // Then ends with
            . '(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’])'
            . ')/i';

        // Find any matching scopes
        preg_match_all($pattern, $target, $matches);
        $targetUrls = collect($matches[0])->map(fn ($url) => rtrim($url, '/'));
        $scope = $this->scopes->firstWhere(fn ($s) => $targetUrls->contains($s->url));

        $issue = Issue::create([
            'project_id' => $this->project->id,
            'scope_id' => $scope ? $scope->id : null,
            'target' => $target,
            'description' => $description,
        ]);

        return new Item([
            'issue_id' => $issue->id,
            'guideline_id' => $guideline_number,
            'assessment' => $assessment,
            'description' => $description,
            'testing_method' => $testing,
            'recommendation' => $recommendation,
            'testing' => $testing,
            'image_links' => $image_links,
            'content_issue' => $content_issue,
        ]);
    }

    public function startRow(): int
    {
        return 4;
    }
}

