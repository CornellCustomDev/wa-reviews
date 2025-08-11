<?php

namespace App\Services\GoogleApi\Helpers;

use Google\Service\Exception;
use Google\Service\Sheets as GoogleSheets;
use Google\Service\Sheets\Sheet as GoogleSheet;
use Google\Service\Sheets\Spreadsheet as GoogleSpreadsheet;
use Google\Service\Sheets\SpreadsheetProperties;

class Spreadsheet
{
    public static function make(string $title): GoogleSpreadsheet
    {
        $spreadsheet = new GoogleSpreadsheet();

        $properties = new SpreadsheetProperties();
        $properties->setTitle($title);

        $spreadsheet->setProperties($properties);

        return $spreadsheet;
    }

    public static function getDefaultSheet(GoogleSpreadsheet $spreadsheet): ?GoogleSheet
    {
        $sheets = $spreadsheet->getSheets() ?? [];

        return $sheets[0] ?? null;
    }

    /**
     * @throws Exception
     */
    public static function create(GoogleSheets $service, GoogleSpreadsheet $spreadsheet): GoogleSpreadsheet
    {
        return $service->spreadsheets->create($spreadsheet);
    }

    /**
     * @throws Exception
     */
    public static function batchUpdate(GoogleSheets $service, GoogleSpreadsheet $spreadsheet, array $updates): string
    {
        $requests = new GoogleSheets\BatchUpdateSpreadsheetRequest();
        $requests->setRequests($updates);

        $response = $service->spreadsheets->batchUpdate($spreadsheet->getSpreadsheetId(), $requests);

        return $response->getSpreadsheetId();
    }
}
