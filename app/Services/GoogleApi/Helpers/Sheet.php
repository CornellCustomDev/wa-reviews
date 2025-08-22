<?php

namespace App\Services\GoogleApi\Helpers;

use Google\Service\Sheets\AddSheetRequest;
use Google\Service\Sheets\AddTableRequest;
use Google\Service\Sheets\Borders;
use Google\Service\Sheets\CellData;
use Google\Service\Sheets\CellFormat;
use Google\Service\Sheets\Color;
use Google\Service\Sheets\ColorStyle;
use Google\Service\Sheets\DimensionProperties;
use Google\Service\Sheets\DimensionRange;
use Google\Service\Sheets\ExtendedValue;
use Google\Service\Sheets\GridProperties;
use Google\Service\Sheets\GridRange;
use Google\Service\Sheets\Link;
use Google\Service\Sheets\MergeCellsRequest;
use Google\Service\Sheets\NumberFormat;
use Google\Service\Sheets\Padding;
use Google\Service\Sheets\Request as SheetsRequest;
use Google\Service\Sheets\RowData;
use Google\Service\Sheets\Sheet as GoogleSheet;
use Google\Service\Sheets\SheetProperties;
use Google\Service\Sheets\Table;
use Google\Service\Sheets\TableRowsProperties;
use Google\Service\Sheets\TextFormat;
use Google\Service\Sheets\TextFormatRun;
use Google\Service\Sheets\TextRotation;
use Google\Service\Sheets\UpdateCellsRequest;
use Google\Service\Sheets\UpdateDimensionPropertiesRequest;
use Google\Service\Sheets\UpdateSheetPropertiesRequest;
use InvalidArgumentException;
use function hexdec;
use function ltrim;
use function strlen;
use function substr;

class Sheet
{
    public static function make(?string $title = null): GoogleSheet
    {
        // Set a sheet ID based on the microtime
        $id =  (int) substr(microtime(true) * 10000, 7, 7);

        $properties = new SheetProperties();
        if ($title) {
            $properties->setTitle($title);
        }
        $properties->setSheetId($id);

        $sheet = new GoogleSheet();
        $sheet->setProperties($properties);

        return $sheet;
    }

    public static function addSheet(GoogleSheet $sheet): SheetsRequest
    {
        $addSheetRequest = new AddSheetRequest();
        $addSheetRequest->setProperties($sheet->getProperties());

        $request = new SheetsRequest();
        $request->setAddSheet($addSheetRequest);

        return $request;
    }

    public static function addTable(string|GridRange $a1range, ?string $name = null, ?TableRowsProperties $rowsProperties = null): SheetsRequest
    {
        $gridRange = $a1range instanceof GridRange ? $a1range
            : static::makeGridRange($a1range);

        $table = new Table();
        $table->setRange($gridRange);
        if ($name) {
            $table->setName($name);
        }
        if ($rowsProperties) {
            $table->setRowsProperties($rowsProperties);
        }

        $addTableRequest = new AddTableRequest();
        $addTableRequest->setTable($table);

        $request = new SheetsRequest();
        $request->setAddTable($addTableRequest);

        return $request;
    }

    public static function setTitle(string $title): SheetsRequest
    {
        $properties = new SheetProperties();
        $properties->setTitle($title);

        $updatePropertiesRequest = new UpdateSheetPropertiesRequest();
        $updatePropertiesRequest->setProperties($properties);
        $updatePropertiesRequest->setFields('title');

        $request = new SheetsRequest();
        $request->setUpdateSheetProperties($updatePropertiesRequest);

        return $request;
    }

    public static function freezeRows(int $rowCount, ?GoogleSheet $sheet = null): SheetsRequest
    {
        $properties = $sheet ? $sheet->getProperties() : new SheetProperties();
        $gridProperties = $properties->getGridProperties() ?? new GridProperties();
        $gridProperties->setFrozenRowCount($rowCount);
        $properties->setGridProperties($gridProperties);

        $updatePropertiesRequest = new UpdateSheetPropertiesRequest();
        $updatePropertiesRequest->setProperties($properties);
        $updatePropertiesRequest->setFields('gridProperties.frozenRowCount');

        $request = new SheetsRequest();
        $request->setUpdateSheetProperties($updatePropertiesRequest);

        return $request;
    }

    public static function value(string|int|float|bool|null $value): CellData
    {
        $value = match (true) {
            is_string($value) => ['stringValue' => $value],
            is_int($value), is_float($value) => ['numberValue' => $value],
            is_bool($value) => ['boolValue' => $value],
            default => ['stringValue' => ''],
        };

        $cell = new CellData();
        $cell->setUserEnteredValue(new ExtendedValue($value));

        return $cell;
    }

    public static function textFormat(
        ?string $foregroundColor = null,
        ?string $fontFamily = null,
        ?int    $fontSize = null,
        ?bool   $bold = false,
        ?bool   $italic = false,
        ?bool   $strikethrough = false,
        ?bool   $underline = false,
        ?string $link = null
    ): CellData
    {
        $textFormat = new TextFormat();

        if ($foregroundColor) {
            $textFormat->setForegroundColorStyle(self::hexToColorStyle($foregroundColor));
        }
        if ($fontFamily) {
            $textFormat->setFontFamily($fontFamily);
        }
        if ($fontSize) {
            $textFormat->setFontSize($fontSize);
        }
        if ($bold) {
            $textFormat->setBold($bold);
        }
        if ($italic) {
            $textFormat->setItalic($italic);
        }
        if ($strikethrough) {
            $textFormat->setStrikethrough($strikethrough);
        }
        if ($underline) {
            $textFormat->setUnderline($underline);
        }
        if ($link) {
            $textFormat->setLink(new Link(['uri' => $link]));
        }

        return static::cellFormat(textFormat: $textFormat);
    }

    public static function textFormatRun(int $startIndex, CellData $format): CellData
    {
        $textFormatRun = new TextFormatRun();
        $textFormatRun->setStartIndex($startIndex);
        $textFormatRun->setFormat($format->getUserEnteredFormat()->getTextFormat());

        $cell = new CellData();
        $cell->setTextFormatRuns([$textFormatRun]);

        return $cell;
    }

    public static function cellFormat(
        ?NumberFormat $numberFormat = null,
        ?string       $backgroundColor = null,
        ?Borders      $borders = null,
        ?Padding      $padding = null,
        ?string       $horizontalAlignment = null,
        ?string       $verticalAlignment = null,
        ?string       $wrapStrategy = null,
        ?string       $textDirection = null,
        ?TextFormat   $textFormat = null,
        ?string       $hyperlinkDisplayType = null,
        ?TextRotation $textRotation = null,
    ): CellData
    {
        $cellFormat = new CellFormat();

        if ($numberFormat) {
            $cellFormat->setNumberFormat($numberFormat);
        }

        if ($backgroundColor) {
            $cellFormat->setBackgroundColorStyle(self::hexToColorStyle($backgroundColor));
        }

        if ($borders) {
            $cellFormat->setBorders($borders);
        }

        if ($padding) {
            $cellFormat->setPadding($padding);
        }

        if ($horizontalAlignment) {
            // LEFT, CENTER, RIGHT
            $cellFormat->setHorizontalAlignment($horizontalAlignment);
        }

        if ($verticalAlignment) {
            // TOP, MIDDLE, BOTTOM
            $cellFormat->setVerticalAlignment($verticalAlignment);
        }

        if ($wrapStrategy) {
            // OVERFLOW_CELL, LEGACY_WRAP, CLIP, WRAP
            $cellFormat->setWrapStrategy($wrapStrategy);
        }

        if ($textDirection) {
            // LEFT_TO_RIGHT, RIGHT_TO_LEFT
            $cellFormat->setTextDirection($textDirection);
        }

        if ($textFormat) {
            $cellFormat->setTextFormat($textFormat);
        }

        if ($hyperlinkDisplayType) {
            // LINKED, PLAIN_TEXT
            $cellFormat->setHyperlinkDisplayType($hyperlinkDisplayType);
        }

        if ($textRotation) {
            $cellFormat->setTextRotation($textRotation);
        }

        $cell = new CellData();
        $cell->setUserEnteredFormat($cellFormat);

        return $cell;
    }

    public static function tableRowsProperties(
        ?string $headerColor = null,
        ?string $firstBandColor = null,
        ?string $secondBandColor = null,
        ?string $footerColor = null
    ): TableRowsProperties
    {
        $props = new TableRowsProperties();

        if ($headerColor) {
            $props->setHeaderColorStyle(self::hexToColorStyle($headerColor));
        }
        if ($firstBandColor) {
            $props->setFirstBandColorStyle(self::hexToColorStyle($firstBandColor));
        }
        if ($secondBandColor) {
            $props->setSecondBandColorStyle(self::hexToColorStyle($secondBandColor));
        }
        if ($footerColor) {
            $props->setFooterColorStyle(self::hexToColorStyle($footerColor));
        }

        return $props;
    }

    public static function updateCells(string|GridRange $a1range, CellData ...$cells): SheetsRequest
    {
        $gridRange = $a1range instanceof GridRange ? $a1range
            : static::makeGridRange($a1range);

        $rowData = new RowData();
        $rowData->setValues($cells);

        $updateRequest = new UpdateCellsRequest();
        $updateRequest->setRows([$rowData]);
        $updateRequest->setFields('*');
        $updateRequest->setRange($gridRange);

        $sheetsRequest = new SheetsRequest();
        $sheetsRequest->setUpdateCells($updateRequest);

        return $sheetsRequest;
    }

    public static function updateColumnWidths(string|GridRange $a1range, int $width): SheetsRequest
    {
        $gridRange = $a1range instanceof GridRange ? $a1range
            : static::makeGridRange($a1range);

        $dimensionRange = new DimensionRange();
        $dimensionRange->setSheetId($gridRange->getSheetId());
        $dimensionRange->setDimension('COLUMNS');
        $dimensionRange->setStartIndex($gridRange->getStartColumnIndex());
        $dimensionRange->setEndIndex($gridRange->getEndColumnIndex());

        $properties = new DimensionProperties();
        $properties->setPixelSize($width);

        $updateRequest = new UpdateDimensionPropertiesRequest();
        $updateRequest->setProperties($properties);
        $updateRequest->setFields('*');
        $updateRequest->setRange($dimensionRange);

        $sheetsRequest = new SheetsRequest();
        $sheetsRequest->setUpdateDimensionProperties($updateRequest);

        return $sheetsRequest;
    }

    public static function mergeCells(string|GridRange $a1range, ?GoogleSheet $sheet = null): SheetsRequest
    {
        $gridRange = $a1range instanceof GridRange ? $a1range
            : static::makeGridRange($a1range);

        $mergeCells = new MergeCellsRequest();
        $mergeCells->setRange($gridRange);
        $mergeCells->setMergeType('MERGE_ALL');

        $request = new SheetsRequest();
        $request->setMergeCells($mergeCells);

        return $request;
    }

    /**
     * Convert an A1-style range (optionally prefixed with a sheet name like "Overview!A1:P1")
     * into a GridRange for the given $sheetId. Supports:
     *  - Single cell:            A1
     *  - Rectangular range:      A1:P10
     *  - Whole column(s):        A:A, A:C
     *  - Whole row(s):           1:1, 1:10
     *  - Open-ended to the edge: A1:P (no end row), A1:10 (no end column), A1: (no end row/col)
     *  - Column-only/row-only single side: A:P, 1: (from row 1 to end)
     *  - Whole single column/row shorthands: A (entire column), 1 (entire row)
     */
    public static function makeGridRange(string $a1, ?GoogleSheet $sheet = null): GridRange
    {
        // Strip optional sheet prefix like "Sheet Name!A1:P1"
        if (str_contains($a1, '!')) {
            $parts = explode('!', $a1, 2);
            $a1 = $parts[1];
        }
        $a1 = strtoupper(trim($a1));

        $range = new GridRange();
        if ($sheet) {
            $range->setSheetId($sheet->getProperties()->getSheetId());
        }

        // Helper lambdas
        $col = function (string $letters): int {
            return self::colLettersToIndex($letters);
        };
        $row = function (string $digits): int {
            return max(0, ((int)$digits) - 1); // A1 rows are 1-based
        };

        // 1) Full column range like "A:A" or "A:C"
        if (preg_match('/^([A-Z]+):([A-Z]+)$/', $a1, $m)) {
            $range->startColumnIndex = $col($m[1]);
            $range->endColumnIndex = $col($m[2]) + 1; // exclusive
            return $range;
        }

        // 2) Full row range like "1:1" or "1:10"
        if (preg_match('/^(\d+):(\d+)$/', $a1, $m)) {
            $range->startRowIndex = $row($m[1]);
            $range->endRowIndex = $row($m[2]) + 1; // exclusive
            return $range;
        }

        // 3) Rectangular range like "A1:P10"
        if (preg_match('/^([A-Z]+)(\d+):([A-Z]+)(\d+)$/', $a1, $m)) {
            $range->startColumnIndex = $col($m[1]);
            $range->startRowIndex = $row($m[2]);
            $range->endColumnIndex = $col($m[3]) + 1; // exclusive
            $range->endRowIndex = $row($m[4]) + 1; // exclusive
            return $range;
        }

        // 4) Single cell like "B7"
        if (preg_match('/^([A-Z]+)(\d+)$/', $a1, $m)) {
            $range->startColumnIndex = $col($m[1]);
            $range->startRowIndex = $row($m[2]);
            $range->endColumnIndex = $col($m[1]) + 1; // single cell => width 1
            $range->endRowIndex = $row($m[2]) + 1; // single cell => height 1
            return $range;
        }

        // 5) Open-ended: "A1:P" (no end row)
        if (preg_match('/^([A-Z]+)(\d+):([A-Z]+)$/', $a1, $m)) {
            $range->startColumnIndex = $col($m[1]);
            $range->startRowIndex = $row($m[2]);
            $range->endColumnIndex = $col($m[3]) + 1;
            // endRowIndex left unset (to sheet end)
            return $range;
        }

        // 6) Open-ended: "A1:10" (no end column)
        if (preg_match('/^([A-Z]+)(\d+):(\d+)$/', $a1, $m)) {
            $range->startColumnIndex = $col($m[1]);
            $range->startRowIndex = $row($m[2]);
            $range->endRowIndex = $row($m[3]) + 1;
            // endColumnIndex left unset (to sheet end)
            return $range;
        }

        // 7) Column band without rows: "A:P"
        if (preg_match('/^([A-Z]+):([A-Z]+)?$/', $a1, $m) && !preg_match('/\d/', $a1)) {
            $range->startColumnIndex = $col($m[1]);
            if (!empty($m[2])) {
                $range->endColumnIndex = $col($m[2]) + 1;
            }
            return $range;
        }

        // 8) Row band without columns: "1:" or "1:10"
        if (preg_match('/^(\d+):(\d+)?$/', $a1, $m) && !preg_match('/[A-Z]/', $a1)) {
            $range->startRowIndex = $row($m[1]);
            if (!empty($m[2])) {
                $range->endRowIndex = $row($m[2]) + 1;
            }
            return $range;
        }

        // 9) Fully open-ended from an anchor: "A1:" (to sheet end)
        if (preg_match('/^([A-Z]+)(\d+):$/', $a1, $m)) {
            $range->startColumnIndex = $col($m[1]);
            $range->startRowIndex = $row($m[2]);
            return $range;
        }

        // 10) Whole single column shorthand: "A" -> entire column
        if (preg_match('/^[A-Z]+$/', $a1)) {
            $idx = $col($a1);
            $range->startColumnIndex = $idx;
            $range->endColumnIndex = $idx + 1;
            return $range;
        }

        // 11) Whole single row shorthand: "1" -> entire row
        if (preg_match('/^\d+$/', $a1)) {
            $idx = $row($a1);
            $range->startRowIndex = $idx;
            $range->endRowIndex = $idx + 1;
            return $range;
        }

        throw new InvalidArgumentException("Unsupported A1 range: {$a1}");
    }

    /**
     * Convert column letters (e.g., 'A', 'Z', 'AA', 'ABC') to a zero-based column index.
     */
    private static function colLettersToIndex(string $letters): int
    {
        $letters = strtoupper(trim($letters));
        if ($letters === '') {
            throw new InvalidArgumentException('Empty column letters');
        }
        $n = 0;
        for ($i = 0, $len = strlen($letters); $i < $len; $i++) {
            $c = ord($letters[$i]);
            if ($c < 65 || $c > 90) { // not A-Z
                throw new InvalidArgumentException("Invalid column letters: {$letters}");
            }
            $n = $n * 26 + ($c - 64); // A=1 ... Z=26
        }
        return $n - 1; // zero-based
    }

    public static function applyFormats(CellData $value, CellData ...$formats): CellData
    {
        foreach ($formats as $format) {
            if ($incoming = $format->getUserEnteredFormat()) {
                $current = $value->getUserEnteredFormat() ?? new CellFormat();

                // merge any text format cells
                if ($incoming->getNumberFormat())        $current->setNumberFormat($incoming->getNumberFormat());
                if ($incoming->getBackgroundColorStyle())$current->setBackgroundColorStyle($incoming->getBackgroundColorStyle());
                if ($incoming->getBorders())             $current->setBorders($incoming->getBorders());
                if ($incoming->getPadding())             $current->setPadding($incoming->getPadding());
                if ($incoming->getHorizontalAlignment()) $current->setHorizontalAlignment($incoming->getHorizontalAlignment());
                if ($incoming->getVerticalAlignment())   $current->setVerticalAlignment($incoming->getVerticalAlignment());
                if ($incoming->getWrapStrategy())        $current->setWrapStrategy($incoming->getWrapStrategy());
                if ($incoming->getTextDirection())       $current->setTextDirection($incoming->getTextDirection());
                if ($incoming->getTextFormat())          $current->setTextFormat($incoming->getTextFormat());
                if ($incoming->getHyperlinkDisplayType())$current->setHyperlinkDisplayType($incoming->getHyperlinkDisplayType());
                if ($incoming->getTextRotation())        $current->setTextRotation($incoming->getTextRotation());

                $value->setUserEnteredFormat($current);
            }

            if ($textFormatRuns = $format->getTextFormatRuns()) {
                // Add text format runs to any that already exist on $value
                $existingRuns = $value->getTextFormatRuns() ?? [];
                array_push($existingRuns, ...$textFormatRuns);

                // textFormatRuns are fragile, so confirm that the runs are valid
                $text = $value->getUserEnteredValue()->getStringValue();
                foreach ($existingRuns as $run) {
                    if ($run->getStartIndex() >= strlen($text)) {
                        throw new InvalidArgumentException('Text format run start index exceeds text length');
                    }
                }

                $value->setTextFormatRuns($existingRuns);
            }
        }

        return $value;
    }

    public static function richTextCell(?string $richText): CellData
    {
        if (blank($richText)) {
            return Sheet::value('');
        }

        // If the content looks like HTML, convert it to text + runs
        if (str_contains($richText, '<')) {
            [$text, $runs] = HtmlToSheetsTextRuns::fromHtml($richText);

            return Sheet::applyFormats(
                Sheet::value($text),
                Sheet::cellFormat(wrapStrategy: 'WRAP'),
                ...$runs,
            );
        }

        // Fallback: treat as plain text
        return Sheet::applyFormats(
            Sheet::value($richText),
            Sheet::cellFormat(wrapStrategy: 'WRAP'),
        );
    }

    /**
     * Recursively extract [startIndex, format] info from whatever structure the CellData encodes.
     * @param array $runs
     * @return array
     */
    public static function extractFormatRuns(array $runs): array
    {
        $out = [];
        foreach ($runs as $run) {
            // Normalize the Google model objects into arrays
            $arr = json_decode(json_encode($run), true);
            Sheet::collectRunsRecursive($arr, $out);
        }
        // Ensure unique by (startIndex,link,bold,italic,underline,fontFamily,fontSize)
        $uniq = [];
        foreach ($out as $r) {
            $key = implode('|', [
                $r['startIndex'] ?? '',
                ($r['link'] ?? ''),
                ($r['bold'] ?? 'n'),
                ($r['italic'] ?? 'n'),
                ($r['underline'] ?? 'n'),
                ($r['fontFamily'] ?? ''),
                ($r['fontSize'] ?? '')
            ]);
            $uniq[$key] = array_filter($r);
        }
        // sort by startIndex to make reasoning predictable
        usort($uniq, function ($a, $b) {
            return ($a['startIndex'] ?? 0) <=> ($b['startIndex'] ?? 0);
        });
        $runs = array_filter($uniq);

        return array_combine(array_map(fn($a) => $a['startIndex'], $runs), $runs);
    }

    private static function collectRunsRecursive(array $node, array &$out): void
    {
        // If this node looks like a TextFormatRun, capture it.
        if (isset($node['startIndex'])) {
            $fmt = $node['format'] ?? [];
            // Link can be either a string or ['uri' => '...'] depending on helper
            $link = null;
            if (isset($fmt['link'])) {
                $link = is_array($fmt['link']) ? ($fmt['link']['uri'] ?? null) : $fmt['link'];
            }
            $out[] = [
                'startIndex' => (int)$node['startIndex'],
                'bold' => $fmt['bold'] ?? null,
                'italic' => $fmt['italic'] ?? null,
                'underline' => $fmt['underline'] ?? null,
                'fontFamily' => $fmt['fontFamily'] ?? null,
                'fontSize' => $fmt['fontSize'] ?? null,
                'link' => $link,
            ];
        }
        foreach ($node as $k => $v) {
            if (is_array($v)) {
                Sheet::collectRunsRecursive($v, $out);
            }
        }
    }

    private static function hexToColorStyle(string $hexColor): ColorStyle
    {
        $colorStyle = new ColorStyle();

        $color = new Color();
        $hex = ltrim($hexColor, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $color->setRed(hexdec(substr($hex, 0, 2)) / 255);
        $color->setGreen(hexdec(substr($hex, 2, 2)) / 255);
        $color->setBlue(hexdec(substr($hex, 4, 2)) / 255);

        $colorStyle->setRgbColor($color);
        return $colorStyle;
    }
}
