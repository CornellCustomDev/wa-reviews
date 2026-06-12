<?php

namespace App\Http\Controllers;

use App\Exports\ProjectReportGoogle;
use App\Models\Project;
use App\Services\GoogleApi\GoogleService;
use Exception;
use Illuminate\Http\Request;

class ReportGoogleController extends Controller
{
    /**
     * @throws Exception
     */
    public function __invoke(Request $request, GoogleService $googleService, Project $project)
    {
        $report = $project->getReviewReport();

        if (!$googleService->ensureAuthorized()) {
            return redirect()->away($googleService->getAuthUrl($request->fullUrl()));
        }

        $sheetsService = $googleService->getSheetsService();
        $driveService = $googleService->getDriveService();
        $spreadsheetId = ProjectReportGoogle::export($report, $sheetsService, $driveService);

        return redirect()->away('https://docs.google.com/spreadsheets/d/' . $spreadsheetId);
    }
}
