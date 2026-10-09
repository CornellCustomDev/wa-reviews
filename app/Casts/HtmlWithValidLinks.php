<?php

namespace App\Casts;

use DOMDocument;
use DOMElement;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Behaves like AsHtmlString, but unwraps links with invalid hrefs (keeping their text) before storage.
 *
 * Invalid hrefs, such as markup pasted into the editor's link field ('mailto:a@b.edu">a@b.edu</a>'),
 * otherwise get stored and cause Google Sheets to reject the entire report export.
 */
class HtmlWithValidLinks implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?HtmlString
    {
        return isset($value) ? new HtmlString($value) : null;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return isset($value)
            ? static::removeInvalidLinks((string) $value, ['model' => $model::class, 'id' => $model->getKey(), 'field' => $key])
            : null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function serialize(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return isset($value) ? (string) $value : null;
    }

    public static function isValidLinkUri(string $uri): bool
    {
        // Characters that are never valid unencoded in a URI (RFC 3986), whitespace, control characters,
        // and percent signs that don't start a valid percent-encoding
        if (preg_match('/[\s\x00-\x1F\x7F"<>\\\\^`{|}]|%(?![0-9A-Fa-f]{2})/u', $uri)) {
            return false;
        }

        if (preg_match('/^https?:/i', $uri)) {
            return filter_var($uri, FILTER_VALIDATE_URL) !== false;
        }

        return (bool) preg_match('/^(mailto|tel):.+/i', $uri);
    }

    /**
     * Replace each link that has an invalid href with its contents. HTML without invalid links is returned unchanged.
     *
     * Each removal is logged as a warning, since this edits user content without an activity record of the original.
     *
     * @param  array{model?: class-string, id?: mixed, field?: string}  $context  Identifies the content being cleaned in the log
     */
    public static function removeInvalidLinks(string $html, array $context = []): string
    {
        if (stripos($html, '<a') === false) {
            return $html;
        }

        // libxml's HTML parser predates HTML5 and reports errors for valid modern tags and for the imperfect
        // markup that editor and AI content can contain. It still builds a usable tree, so its errors and
        // warnings are suppressed. The XML declaration makes it read the input as UTF-8, the wrapper div lets
        // us serialize just the fragment back out, and LIBXML_NONET blocks network access during parsing.
        $dom = new DOMDocument('1.0', 'UTF-8');
        @$dom->loadHTML('<?xml encoding="utf-8" ?><div id="html-with-valid-links">'.$html.'</div>', LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);

        $invalidLinks = [];
        foreach ($dom->getElementsByTagName('a') as $link) {
            $href = $link->getAttribute('href');
            if ($href !== '' && ! static::isValidLinkUri($href)) {
                $invalidLinks[] = $link;
            }
        }

        if (empty($invalidLinks)) {
            return $html;
        }

        /** @var DOMElement $link */
        foreach ($invalidLinks as $link) {
            Log::warning('Removed invalid link from HTML content', [
                ...$context,
                'href' => $link->getAttribute('href'),
                'text' => Str::limit(trim($link->textContent), 100),
            ]);

            while ($link->firstChild) {
                $link->parentNode->insertBefore($link->firstChild, $link);
            }
            $link->parentNode->removeChild($link);
        }

        $output = '';
        foreach ($dom->getElementById('html-with-valid-links')->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }

        return $output;
    }
}
