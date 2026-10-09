<?php

namespace App\Services\GoogleApi\Helpers;

use DOMDocument;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use League\Uri\Contracts\UriException;
use Stringable;

/**
 * Google Sheets rejects an entire report batchUpdate if any link URI is invalid, e.g. markup pasted into
 * the editor's link field ('mailto:a@b.edu">a@b.edu</a>'). These helpers find such links so users can fix them.
 */
class SheetLinks
{
    /**
     * Parse a link URI per RFC 3986, percent-encoding characters that need it (e.g. spaces).
     *
     * @return string|null The normalized URI, or null if it can't be parsed or isn't an absolute http(s), mailto, or tel link
     */
    public static function normalize(string $uri): ?string
    {
        try {
            $parsed = Uri::of($uri);
        } catch (UriException) {
            return null;
        }

        $isLink = match (strtolower($parsed->scheme() ?? '')) {
            'http', 'https' => filled($parsed->host()),
            'mailto', 'tel' => true,
            default => false,
        };

        return $isLink ? $parsed->value() : null;
    }

    public static function isValid(string $uri): bool
    {
        return static::normalize($uri) !== null;
    }

    /**
     * Find the links in an HTML fragment whose hrefs can't be exported.
     *
     * @return list<array{text: string, href: string}>
     */
    public static function invalidLinksInHtml(string|Stringable|null $html): array
    {
        $html = (string) $html;
        if (stripos($html, '<a') === false) {
            return [];
        }

        // libxml's HTML parser predates HTML5 and reports errors for valid modern tags and for the imperfect
        // markup that editor and AI content can contain. It still builds a usable tree, so its errors and
        // warnings are suppressed. The XML declaration makes it read the input as UTF-8, and LIBXML_NONET
        // blocks network access during parsing.
        $dom = new DOMDocument('1.0', 'UTF-8');
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);

        $invalidLinks = [];
        foreach ($dom->getElementsByTagName('a') as $link) {
            $href = $link->getAttribute('href');
            if ($href !== '' && ! static::isValid($href)) {
                $invalidLinks[] = ['text' => Str::limit(trim($link->textContent), 100), 'href' => $href];
            }
        }

        return $invalidLinks;
    }

}
