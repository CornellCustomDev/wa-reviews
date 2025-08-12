<?php

namespace Tests\Unit\GoogleApi;

use App\Services\GoogleApi\Helpers\HtmlToSheetsTextRuns;
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
        $formatRuns = $this->extractFormatRuns($runs);

        // Helper to count runs with a specific format
        $getFormat = function ($format, $value = null) use ($formatRuns) {
            $formatRuns = array_filter($formatRuns, fn($r) => $r[$format] ?? false);
            if ($value !== null) {
                $formatRuns = array_filter($formatRuns, fn($r) => $r[$format] === $value);
            }
            return $formatRuns;
        };


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

    /**
     * Recursively extract [startIndex, format] info from whatever structure the CellData encodes.
     */
    private function extractFormatRuns(array $runs): array
    {
        $out = [];
        foreach ($runs as $run) {
            // Normalize the Google model objects into arrays
            $arr = json_decode(json_encode($run), true);
            $this->collectRunsRecursive($arr, $out);
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

        return array_combine(array_map(fn ($a) => $a['startIndex'], $runs), $runs);
    }

    private function collectRunsRecursive(array $node, array &$out): void
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
                'startIndex'  => (int) $node['startIndex'],
                'bold'        => $fmt['bold'] ?? null,
                'italic'      => $fmt['italic'] ?? null,
                'underline'   => $fmt['underline'] ?? null,
                'fontFamily'  => $fmt['fontFamily'] ?? null,
                'fontSize'    => $fmt['fontSize'] ?? null,
                'link'        => $link,
            ];
        }
        foreach ($node as $k => $v) {
            if (is_array($v)) {
                $this->collectRunsRecursive($v, $out);
            }
        }
    }

    #[Test] public function returns_empty_sheet_value_for_empty_html(): void
    {
        $html = '<p></p>'; // Empty paragraph

        [$text, $runs] = HtmlToSheetsTextRuns::fromHtml($html);

        $this->assertEquals('', $text);

        $this->assertIsArray($runs);
        $this->assertEmpty($runs);
    }


}
