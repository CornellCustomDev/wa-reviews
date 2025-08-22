<?php

namespace App\Services\GoogleApi\ServiceWrappers;

use Google\Service\Drive as GoogleDrive;
use Google\Service\Exception as GoogleException;
use Google\Service\Sheets as GoogleSheets;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\Request as SheetsRequest;
use Google\Service\Sheets\Spreadsheet as GoogleSpreadsheet;
use InvalidArgumentException;

class SheetUpdates
{
    /**
     * @throws GoogleException
     */
    public static function batchUpdate(GoogleSheets $service, GoogleSpreadsheet $spreadsheet, array $updates): string
    {
        foreach ($updates as $request) {
            if (!($request instanceof SheetsRequest)) {
                throw new InvalidArgumentException('All requests must be instances of Google\Service\Sheets\Request');
            }
        }

        $requests = new BatchUpdateSpreadsheetRequest();
        $requests->setRequests($updates);

        $response = $service->spreadsheets->batchUpdate($spreadsheet->getSpreadsheetId(), $requests);

        return $response->getSpreadsheetId();
    }

    /**
     * @throws GoogleException
     */
    public static function create(GoogleSheets $service, GoogleSpreadsheet $spreadsheet): GoogleSpreadsheet
    {
        return $service->spreadsheets->create($spreadsheet);
    }
    
    /**
     * @throws GoogleException
     */
    public static function delete(GoogleDrive $service, GoogleSpreadsheet $spreadsheet): void
    {
        $service->files->delete($spreadsheet->getSpreadsheetId());
    }

}
