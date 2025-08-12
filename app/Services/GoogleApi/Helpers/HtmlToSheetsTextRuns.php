<?php

namespace App\Services\GoogleApi\Helpers;

class HtmlToSheetsTextRuns
{
    /**
     * Parse controlled HTML (p, h1-3, strong/b, em/i, u, ul/ol/li, blockquote, a, code)
     * into a flat text string and an array of TextFormat runs compatible with Sheet::textFormatRun().
     *
     * @return array{0:string,1:array{\Google\Service\Sheets\CellData}}
     */
    public static function fromHtml(string $html): array
    {
        // Normalize whitespace and ensure UTF-8
        $html = trim($html);
        if ($html === '') {
            return ['', []];
        }

        // Load HTML into DOM
        $dom = new \DOMDocument('1.0', 'UTF-8');
        // Suppress warnings due to HTML5 tags; input is controlled
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);

        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body) {
            return [$html, []];
        }

        $out = '';
        $runs = [];

        // Track the most recently emitted effective style so we can start a new run when it changes
        $lastEmittedStyle = null;

        // Style stack representing cumulative formatting
        $base = [
            'bold' => false,
            'italic' => false,
            'underline' => false,
            'fontSize' => null,
            'fontFamily' => null,
            'link' => null,
        ];

        $numberStack = []; // track <ol> numbering levels

        $appendText = function (string $text, array $style) use (&$out, &$runs, &$lastEmittedStyle) {
            if ($text === '') return;

            $start = mb_strlen($out, 'UTF-8');

            // Normalize the effective style we intend to apply for this segment
            $effective = [
                'bold'       => (bool)($style['bold'] ?? false),
                'italic'     => (bool)($style['italic'] ?? false),
                'underline'  => (bool)($style['underline'] ?? false),
                'fontSize'   => $style['fontSize'] ?? null,
                'fontFamily' => $style['fontFamily'] ?? null,
                'link'       => $style['link'] ?? null,
            ];

            // If this is the first segment or the effective style changed, start a new run at $start.
            if ($lastEmittedStyle === null || $effective !== $lastEmittedStyle) {
                $format = Sheet::textFormat(
                    foregroundColor: null,
                    fontFamily: $effective['fontFamily'],
                    fontSize: $effective['fontSize'],
                    bold: $effective['bold'],
                    italic: $effective['italic'],
                    strikethrough: false,
                    underline: $effective['underline'],
                    link: $effective['link']
                );
                $runs[] = Sheet::textFormatRun($start, $format);
                $lastEmittedStyle = $effective;
            }

            $out .= $text;
        };

        $walk = function (\DOMNode $node, array $style) use (&$walk, &$appendText, &$numberStack) {
            $name = strtolower($node->nodeName);

            switch ($name) {
                case 'h1':
                    $style['bold'] = true;
                    $style['fontSize'] = 16; // tune if needed
                    break;
                case 'h2':
                    $style['bold'] = true;
                    $style['fontSize'] = 14;
                    break;
                case 'h3':
                    $style['bold'] = true;
                    $style['fontSize'] = 12;
                    break;
                case 'strong':
                case 'b':
                    $style['bold'] = true;
                    break;
                case 'em':
                case 'i':
                    $style['italic'] = true;
                    break;
                case 'u':
                    $style['underline'] = true;
                    break;
                case 'a':
                    if ($node instanceof \DOMElement && $node->hasAttribute('href')) {
                        $style['link'] = $node->getAttribute('href');
                    }
                    break;
                case 'code':
                    $style['fontFamily'] = 'Courier New';
                    break;
                case 'blockquote':
                    // Use a leading quote and italicize by default
                    $style['italic'] = true;
                    break;
                case 'ol':
                    array_push($numberStack, 1);
                    break;
                case 'ul':
                    // bullets handled in <li>
                    break;
            }

            // Emit block prefix (list bullets/numbers, blockquote marker) for LI specifically
            if ($name === 'li') {
                $parent = $node->parentNode ? strtolower($node->parentNode->nodeName) : '';
                if ($parent === 'ul') {
                    $appendText("• ", $style);
                } elseif ($parent === 'ol') {
                    $level = count($numberStack);
                    $n = $level > 0 ? $numberStack[$level - 1] : 1;
                    $appendText($n . '. ', $style);
                    if ($level > 0) {
                        $numberStack[$level - 1] = $n + 1;
                    }
                }
            }

            // Walk children
            foreach ($node->childNodes as $child) {
                if ($child->nodeType === XML_TEXT_NODE) {
                    $text = preg_replace("/\s+/u", ' ', $child->nodeValue ?? '');
                    // Preserve intentional newlines inside code blocks
                    if (strtolower($node->nodeName) === 'code') {
                        $text = str_replace(['\r\n', '\r'], "\n", $child->nodeValue ?? '');
                    }
                    $appendText($text, $style);
                } else {
                    $walk($child, $style);
                }
            }

            // Block-level line breaks after certain tags
            if (in_array($name, ['p', 'h1', 'h2', 'h3', 'blockquote', 'li'])) {
                $appendText("\n", $style);
            }

            if ($name === 'ol') {
                array_pop($numberStack);
            }
        };

        // Start walking children of <body>
        foreach (iterator_to_array($body->childNodes) as $child) {
            $walk($child, $base);
        }

        // Collapse multiple trailing newlines to a single one and trim
        $out = preg_replace("/\n{3,}/", "\n\n", $out);
        $out = rtrim($out, "\n");
        if (empty($out)) {
            $runs = [];
        }

        return [$out, $runs];
    }
}
