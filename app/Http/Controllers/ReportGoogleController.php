<?php

namespace App\Http\Controllers;

use App\Exports\ProjectReportGoogle;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Scope;
use App\Services\GoogleApi\GoogleService;
use App\Services\GoogleApi\Helpers\SheetLinks;
use Exception;
use Illuminate\Http\Request;

class ReportGoogleController extends Controller
{
    /**
     * @throws Exception
     */
    public function __invoke(Request $request, GoogleService $googleService, Project $project)
    {
        // Google Sheets rejects the whole export if any link is invalid, so have the user fix them first
        $invalidLinksDescriptions = self::describeInvalidLinks($project);
        if ($invalidLinksDescriptions) {
            return redirect()->route('project.report', $project)->with('warning', [
                'heading' => 'This report can’t be exported to Google Sheets until these invalid links are fixed:',
                'details' => $invalidLinksDescriptions,
            ]);
        }

        if (!$googleService->ensureAuthorized()) {
            return redirect()->away($googleService->getAuthUrl($request->fullUrl()));
        }

        $sheetsService = $googleService->getSheetsService();
        $driveService = $googleService->getDriveService();
        $spreadsheetId = ProjectReportGoogle::export($project, $sheetsService, $driveService);

        return redirect()->away('https://docs.google.com/spreadsheets/d/' . $spreadsheetId);
    }

    /**
     * Describe the links in the report that Google Sheets would reject, which must be fixed before exporting.
     *
     * Covers every link the export sends, with labels matching the fields users edit, and the record linked to
     * the page where it can be fixed.
     *
     * @return list<string> HTML, e.g. '<a href="…">Issue 1.4.3-2</a>, Recommendations: &quot;Florencia Marcucci&quot;'
     */
    public static function describeInvalidLinks(Project $project): array
    {
        $invalidLinkDescriptions = [];

        if ($project->site_url && !SheetLinks::isValid($project->site_url)) {
            $invalidLinkDescriptions[] = static::describeRecordLink('Project', route('project.edit', $project), "Site URL: $project->site_url");
        }
        if ($project->siteimprove_url && !SheetLinks::isValid($project->siteimprove_url)) {
            $invalidLinkDescriptions[] = static::describeRecordLink('Project', route('project.edit', $project), "Siteimprove Report URL: $project->siteimprove_url");
        }

        /** @var Issue $issue */
        foreach ($project->getReportableIssues() as $issue) {
            foreach (ProjectReportGoogle::findIssueFieldsWithInvalidLinks($issue) as $issueFieldsDescription) {
                $invalidLinkDescriptions[] = static::describeRecordLink('Issue ' . $issue->getGuidelineInstanceNumber(), route('issue.show', $issue), $issueFieldsDescription);
            }
        }

        /** @var Scope $scope */
        foreach ($project->scopes()->get() as $scope) {
            foreach (ProjectReportGoogle::findScopeFieldsWithInvalidLinks($scope) as $scopeFieldsDescription) {
                $invalidLinkDescriptions[] = static::describeRecordLink('Scope "' . $scope->title . '"', route('scope.show', $scope), $scopeFieldsDescription);
            }
        }

        return $invalidLinkDescriptions;
    }
    /**
     * @return string HTML with the record label linked and all text escaped
     */
    public static function describeRecordLink(string $recordLabel, string $url, string $fieldLink): string
    {
        return '<a href="' . e($url) . '">' . e($recordLabel) . '</a>, ' . e($fieldLink);
    }
}
