<?php

namespace App\Exports;

use App\Enums\Assessment;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Scope;
use App\Services\GoogleApi\Helpers\Sheet;
use App\Services\GoogleApi\Helpers\Spreadsheet;
use App\Services\GoogleApi\ServiceWrappers\SheetUpdates;
use Exception;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Exception as GoogleException;
use Google\Service\Sheets as GoogleSheets;
use Google\Service\Sheets\Sheet as GoogleSheet;
use Illuminate\Support\Str;

class ProjectReportGoogle
{
    /**
     * @throws Exception
     */
    public static function export(Project $project, GoogleSheets $sheetsService, GoogleDrive $driveService): string
    {
        // Get the report data first, in case there are issues.
        $updates = [Sheet::setTitle('Final Checklist')];
        $updates = [...$updates, ...static::getIntroFieldUpdates($project)];
        $updates = [...$updates, ...static::getIssuesHeader()];
        $updates = [...$updates, ...static::getIssueValues($project)];

        // Get the scope data
        $scopeSheet = Sheet::make('Scope');
        $updates = [...$updates, Sheet::addSheet($scopeSheet)];
        $updates = [...$updates, ...static::getScopeIntroFields($project, $scopeSheet)];
        $updates = [...$updates, ...static::getScopesHeader($scopeSheet)];
        $updates = [...$updates, ...static::getScopeValues($project, $scopeSheet)];

        // Store in Google Sheets
        $googleSpreadsheet = Spreadsheet::make("WA Report - $project->name");
        $spreadsheet = SheetUpdates::create($sheetsService, $googleSpreadsheet);

        try {
            SheetUpdates::batchUpdate($sheetsService, $spreadsheet, $updates);
        } catch (GoogleException $e) {
            SheetUpdates::delete($driveService, $spreadsheet);
            throw new Exception('Failed to update Google Sheets: ' . $e->getMessage(), 0, $e);
        }

        return $spreadsheet->spreadsheetId;
    }

    private static function getIntroFieldUpdates(Project $project): array
    {
        $updates = [];

        $updates[] = Sheet::updateCells('A1',
            Sheet::applyFormats(
                Sheet::value('Web Accessibility Assessment Review'),
                Sheet::textFormat(fontSize: 16, bold: true)
            )
        );
        $updates[] = Sheet::mergeCells('A1:P1');

        $updates[] = Sheet::updateCells('A2',
            Sheet::applyFormats(
                Sheet::value('Project: ' . $project->name),
                Sheet::textFormat(fontSize: 14)
            )
        );
        $updates[] = Sheet::mergeCells('A2:P2');

        $updates[] = Sheet::updateCells('A3',
            Sheet::applyFormats(
                Sheet::value('Prepared by: ' . $project->reviewer->name . ' (' . $project->reviewer->email . ')'),
                Sheet::textFormat(italic: true)
            )
        );
        $updates[] = Sheet::mergeCells('A3:D3');

        $updates[] = Sheet::updateCells('A4',
            Sheet::applyFormats(
                Sheet::value('Date review completed: ' . ($project->completed_at?->format('F j, Y') ?? '')),
                Sheet::textFormat(italic: true),
            )
        );
        $updates[] = Sheet::mergeCells('A4:D4');

        $updates[] = Sheet::updateCells('A5',
            Sheet::value(''),
        ); // Empty row for spacing

        $text = 'Site URL: ';
        $updates[] = Sheet::updateCells('A6',
            Sheet::applyFormats(
                Sheet::value($text . ($project->site_url ?? 'Not provided')),
                Sheet::textFormatRun(0, Sheet::textFormat(bold: true)),
                Sheet::textFormatRun(strlen($text), Sheet::textFormat(link: $project->site_url)),
            )
        );
        $updates[] = Sheet::mergeCells('A6:D6');

        $updates[] = Sheet::updateCells('A7',
            Sheet::value(''),
        ); // Empty row for spacing

        return $updates;
    }

    private static function getIssuesHeader(): array
    {
        $updates = [];

        $updates[] = Sheet::updateCells('A8',
            Sheet::applyFormats(
                Sheet::value('List of Issues Found'),
                Sheet::textFormat(fontSize: 14, bold: true),
            )
        );
        $updates[] = Sheet::mergeCells('A8:P8');

        $headers = [
            'ID', 'Criterion', 'Description', 'Pass', 'Warn', 'Fail', 'N/A',
            'Impact', 'Scope', 'Location', 'Observation', 'Recommendation',
            'Testing', 'Images', 'CE Issue', 'Barrier Mitigation Required'
        ];
        $values = [];
        foreach ($headers as $field) {
            $values[] = Sheet::applyFormats(
                Sheet::value($field),
                Sheet::cellFormat(backgroundColor: '#d9d9d9'),
                Sheet::textFormat(bold: true)
            );
        }
        $updates[] = Sheet::updateCells('A9:P9', ...$values);

        // Column widths (in pixels)
        $updates[] = Sheet::updateColumnWidths('A', 75); // ID
        $updates[] = Sheet::updateColumnWidths('B', 200); // Criterion
        $updates[] = Sheet::updateColumnWidths('C', 500); // Description
        $updates[] = Sheet::updateColumnWidths('D:G', 50); // Pass, Warn, Fail, N/A
        $updates[] = Sheet::updateColumnWidths('H', 100); // Impact
        $updates[] = Sheet::updateColumnWidths('I', 200); // Scope
        $updates[] = Sheet::updateColumnWidths('J', 350); // Location
        $updates[] = Sheet::updateColumnWidths('K:L', 500); // Observation, Recommendation
        $updates[] = Sheet::updateColumnWidths('M', 350); // Testing
        $updates[] = Sheet::updateColumnWidths('N', 350); // Images
        $updates[] = Sheet::updateColumnWidths('O', 50); // CE Issue
        $updates[] = Sheet::updateColumnWidths('P', 300); // Barrier Mitigation Required

        $updates[] = Sheet::freezeRows(9);

        return $updates;
    }

    private static function getIssueValues(Project $project): array
    {
        $updates = [];

        $issues = $project->getReportableIssues();
        $index = 0;
        /** @var Issue $issue */
        foreach ($issues as $issue) {
            $issueUrl = route('issue.show', $issue);
            $issueIdentifier = $issue->getGuidelineInstanceNumber();

            $scope = '';
            $scopeFormats = [];
            if ($issue->scope) {
                $scope = $issue->scope->title;
                $scopeFormats[] = Sheet::textFormatRun(0, Sheet::textFormat(bold: true));
                // If the scope has a URL, add it to the scope text and formats
                if ($issue->scope->url) {
                    $scope .= "\n" . $issue->scope->url;
                    $scopeFormats[] = Sheet::textFormatRun(
                        Str::length($issue->scope->title),
                        Sheet::textFormat(link: $issue->scope->url)
                    );
                }
            }

            $testing = '';
            if ($issue->testing && !($issue->testing->isEmpty())) {
                $testing = $issue->testing;
            } elseif ($issue->testing_method) {
                $testing = $issue->testing_method->value();
            }

            $linksText = '';
            $linksFormat = [];
            if ($issue->image_links) {
                foreach ($issue->image_links as $imagePath) {
                    $filename = pathinfo($imagePath, PATHINFO_BASENAME);
                    $linksFormat[] = Sheet::textFormatRun(
                        Str::length($linksText),
                        Sheet::textFormat(link: $imagePath)
                    );
                    $linksText .= $filename . "\n";
                }
                $linksText = rtrim($linksText, "\n");
            }

            $values = [
                Sheet::applyFormats(
                    Sheet::value($issueIdentifier),
                    Sheet::textFormatRun(0, Sheet::textFormat(link: $issueUrl)),
                ),
                Sheet::applyFormats(
                    Sheet::value($issue->guideline->criterion->getLongName()),
                    Sheet::cellFormat(wrapStrategy: 'WRAP')
                ),
                Sheet::applyFormats(
                    Sheet::value($issue->guideline->name),
                    Sheet::cellFormat(wrapStrategy: 'WRAP')
                ),
                Sheet::applyFormats(
                    Sheet::value(($issue->assessment == Assessment::Pass) ? 'X' : ''),
                    Sheet::cellFormat(backgroundColor: '#caff37', horizontalAlignment: 'CENTER')
                ),
                Sheet::applyFormats(
                    Sheet::value(($issue->assessment == Assessment::Warn) ? 'X' : ''),
                    Sheet::cellFormat(backgroundColor: '#f6b26b', horizontalAlignment: 'CENTER')
                ),
                Sheet::applyFormats(
                    Sheet::value(($issue->assessment == Assessment::Fail) ? 'X' : ''),
                    Sheet::cellFormat(backgroundColor: '#ea9999', horizontalAlignment: 'CENTER')
                ),
                Sheet::applyFormats(
                    Sheet::value(($issue->assessment == Assessment::Not_Applicable) ? 'X' : ''),
                    Sheet::cellFormat(backgroundColor: '#9fc5e8', horizontalAlignment: 'CENTER')
                ),
                Sheet::value($issue->impact ? $issue->impact->value() : ''),
                Sheet::applyFormats(
                    Sheet::value($scope),
                    Sheet::cellFormat(wrapStrategy: 'WRAP'),
                    ...$scopeFormats,
                ),
                Sheet::applyFormats(
                    Sheet::value($issue->target),
                    Sheet::cellFormat(wrapStrategy: 'WRAP'),
                ),
                Sheet::richTextCell($issue->description),
                Sheet::richTextCell($issue->recommendation),
                Sheet::richTextCell($testing),
                Sheet::applyFormats(
                    Sheet::value($linksText),
                    Sheet::cellFormat(wrapStrategy: 'WRAP'),
                    ...$linksFormat,
                ),
                Sheet::value(!empty($issue->ce_issue) ? 'X' : ''),
                Sheet::value(!empty($issue->needs_mitigation) ? 'X' : ''),
            ];

            $updates[] = Sheet::updateCells(
                'A' . (10 + $index).':P' . (10 + $index),
                ...$values
            );
            $index++;
        }

        return $updates;
    }

    private static function getScopeIntroFields(Project $project, GoogleSheet $sheet): array
    {
        $updates = [];

        $updates[] = Sheet::updateCells(Sheet::makeGridRange('A1', $sheet),
            Sheet::applyFormats(
                Sheet::value('Scope: ' . $project->name),
                Sheet::textFormatRun(0, Sheet::textFormat(fontSize: 14))
            )
        );
        $updates[] = Sheet::mergeCells(Sheet::makeGridRange('A1:C1', $sheet));

        $siteImproveText = 'Siteimprove a11y Report: ';
        if ($project->siteimprove_url) {
            $format = Sheet::textFormat(link: $project->siteimprove_url);
            $report = $project->siteimprove_url;
        } else {
            $format = Sheet::textFormat();
            $report = 'Not provided';
        }
        $siteimproveFormat = [
            Sheet::textFormatRun(0, Sheet::textFormat(bold: true)),
            Sheet::textFormatRun(Str::length($siteImproveText), $format),
        ];

        $updates[] = Sheet::updateCells(Sheet::makeGridRange('A2', $sheet),
            Sheet::applyFormats(
                Sheet::value($siteImproveText . $report),
                ...$siteimproveFormat,
            )
        );
        $updates[] = Sheet::mergeCells(Sheet::makeGridRange('A2:C2', $sheet));

        return $updates;
    }

    private static function getScopesHeader(GoogleSheet $sheet): array
    {
        // Screen Title, Type, URL if applicable, Notes on specific screens/pages, Reviewer Comments
        $updates = [];
        $headers = [
            'Scope', 'Notes on specific screens/pages', 'Reviewer Comments'
        ];
        $values = [];
        foreach ($headers as $field) {
            $values[] = Sheet::applyFormats(
                Sheet::value($field),
                Sheet::cellFormat(backgroundColor: '#d9d9d9'),
                Sheet::textFormat(bold: true)
            );
        }
        $updates[] = Sheet::updateCells(Sheet::makeGridRange('A4:C4', $sheet), ...$values);

        // Column widths (in pixels)
        $updates[] = Sheet::updateColumnWidths(Sheet::makeGridRange('A', $sheet), 500); // Scope
        $updates[] = Sheet::updateColumnWidths(Sheet::makeGridRange('B', $sheet), 350); // Notes on specific screens/pages
        $updates[] = Sheet::updateColumnWidths(Sheet::makeGridRange('C', $sheet), 300); // Reviewer Comments

        $updates[] = Sheet::freezeRows(4, $sheet);

        return $updates;
    }

    private static function getScopeValues(Project $project, GoogleSheet $sheet): array
    {
        $updates = [];

        $scopes = $project->scopes()->get();
        $index = 0;
        /** @var Scope $scope */
        foreach ($scopes as $scope) {
            $scopeText = $scope->title;
            $scopeFormats = [Sheet::textFormatRun(0, Sheet::textFormat(bold: true))];
            if ($scope->url) {
                $scopeText .= "\n";
                $scopeFormats[] = Sheet::textFormatRun(
                    Str::length($scopeText),
                    Sheet::textFormat(link: $scope->url)
                );
                $scopeText .= $scope->url;
            }
            $values = [
                Sheet::applyFormats(
                    Sheet::value($scopeText),
                    Sheet::cellFormat(wrapStrategy: 'WRAP'),
                    ...$scopeFormats,
                ),
                Sheet::richTextCell($scope->notes),
                Sheet::richTextCell($scope->comments),
            ];
            $updates[] = Sheet::updateCells(
                Sheet::makeGridRange('A' . (5 + $index) . ':C' . (5 + $index), $sheet),
                ...$values
            );
            $index++;
        }

        return $updates;
    }

}
