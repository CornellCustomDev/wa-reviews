<?php

namespace Tests\Unit\GoogleApi;

use App\Services\GoogleApi\Helpers\HtmlToSheetsTextRuns;
use App\Services\GoogleApi\Helpers\Sheet;
use Google\Service\Sheets\CellData;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HtmlToSheetsTest extends TestCase
{
    #[Test] public function parses_html_to_sheets(): void
    {
        $html = '<p>Basic test</p><h1>Heading 1</h1><h2>Heading 2</h2><h3>Heading 3</h3><p><strong>Bold</strong>, <em>Italic</em>, <u>Underline</u></p><ul><li><p>Bulleted 1</p></li><li><p>Bulleted 2</p></li></ul><ol><li><p>Numbered 1</p></li><li><p>Numbered 2</p></li></ol><blockquote><p>Blockquote</p></blockquote><p><a target="_blank" rel="noopener noreferrer nofollow" href="https://example.com">Link</a></p><p><code>$code = \'sample\';</code></p><p>End</p>';

        [$text, $runs] = HtmlToSheetsTextRuns::fromHtml($html);

        // 1) Text basics: contains expected structure
        $this->assertStringContainsString("Basic test\nHeading 1\nHeading 2\nHeading 3\n", $text);
        $this->assertStringContainsString("• Bulleted 1", $text);
        $this->assertStringContainsString("1. Numbered 1", $text);
        $this->assertStringContainsString("Blockquote", $text);
        $this->assertStringContainsString("\$code = 'sample';", $text);
        $this->assertStringEndsWith('End', $text);

        // 2) Runs basics: non-empty and are CellData objects
        $this->assertIsArray($runs);
        $this->assertNotEmpty($runs);
        foreach ($runs as $run) {
            $this->assertInstanceOf(CellData::class, $run);
        }

        // 3) Formatting presence checks
        $formatRuns = Sheet::extractFormatRuns($runs);
        $getFormat = $this->filterFormats($formatRuns);

        // Find the run that sets italic:true for the blockquote.
        $ixBlockquote = mb_strpos($text, 'Blockquote', 0, 'UTF-8');
        $italics = $getFormat('italic', true);
        $this->assertArrayHasKey($ixBlockquote, $italics, "Expected an italic run to begin exactly at the start of 'Blockquote'.");

        // Find the run corresponding to the hyperlink on "Link".
        $ixLink = mb_strpos($text, 'Link', 0, 'UTF-8');
        $links = $getFormat('link', 'https://example.com');
        $this->assertArrayHasKey($ixLink, $links, "Expected a link run to begin exactly at the start of 'Link'.");

        $this->assertTrue($getFormat('fontSize', 16) && $getFormat('fontSize', 14) && $getFormat('fontSize', 12));
        $this->assertEquals(4, count($getFormat('bold')), 'Expected 4 bold runs (headings and <strong>).');
        // There should be 2 italics (one for <em> and one for blockquote).
        $this->assertEquals(2, count($getFormat('italic')), 'Expected 2 italic runs (one for <em> and one for blockquote).');
        // There should be 1 underline (for <u>).
        $this->assertEquals(1, count($getFormat('underline')), 'Expected 1 underline run (for <u>).');
        // There should be 1 monospace font (for <code>).
        $this->assertEquals(1, count($getFormat('fontFamily', 'Courier New')), 'Expected 1 monospace font run (for <code>).');
        // There should be 1 link (for the hyperlink).
        $this->assertEquals(1, count($getFormat('link', 'https://example.com')), 'Expected 1 link run to include the href URI (for the hyperlink).');
    }

    #[Test] public function returns_empty_sheet_value_for_empty_html(): void
    {
        $html = '<p></p>'; // Empty paragraph

        [$text, $runs] = HtmlToSheetsTextRuns::fromHtml($html);

        $this->assertEquals('', $text);

        $this->assertIsArray($runs);
        $this->assertEmpty($runs);
    }

    #[Test] public function only_one_format_for_nested_link(): void
    {
        $html = '<p>The headers attribute must be applied to each cell, referencing all table headers that are relevant to this cell.  <a target="_blank" rel="noopener noreferrer nofollow" href="https://www.w3.org/WAI/tutorials/tables/multi-level/">https://www.w3.org/WAI/tutorials/tables/multi-level/</a></p>';

        [$text, $runs] = HtmlToSheetsTextRuns::fromHtml($html);

        $this->assertStringContainsString('https://www.w3.org/WAI/tutorials/tables/multi-level/', $text);

        $formatRuns = Sheet::extractFormatRuns($runs);
        $getFormat = $this->filterFormats($formatRuns);

        $this->assertEquals(1, count($getFormat('link', 'https://www.w3.org/WAI/tutorials/tables/multi-level/')), 'Expected 1 link run to include the href URI.');
        $this->assertEquals(1, count($formatRuns), 'Expected only one format run to be present.');
    }

    /**
     * Returns a closure that can be used to filter format runs by a specific format and optional value.
     */
    private function filterFormats(array $formatRuns): \Closure
    {
        $getFormat = function ($format, $value = null) use ($formatRuns) {
            $formatRuns = array_filter($formatRuns, fn($r) => $r[$format] ?? false);
            if ($value !== null) {
                $formatRuns = array_filter($formatRuns, fn($r) => $r[$format] === $value);
            }
            return $formatRuns;
        };

        return $getFormat;
    }

}
