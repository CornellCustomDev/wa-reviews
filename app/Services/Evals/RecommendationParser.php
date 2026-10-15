<?php

namespace App\Services\Evals;

/**
 * Tolerantly reads a guideline recommendation payload out of model text.
 *
 * The recommender's free text is later normalised by `StructuredOutputAgent`,
 * so its raw output may be JSON, fenced JSON, or prose containing JSON. For
 * evaluation we only need the guideline numbers and the feedback string, so a
 * regex fallback is preferable to discarding an otherwise usable response.
 */
class RecommendationParser
{
    /**
     * @return array{guidelines: array<int, array<string, mixed>>, feedback: ?string, guideline_ids: array<int, int>, malformed_numbers: array<int, string>, parse_method: string}
     */
    public static function parse(?string $text): array
    {
        $decoded = self::decode($text);

        if (is_array($decoded)) {
            $guidelines = array_map(
                fn ($guideline) => (array) $guideline,
                array_values(array_filter(
                    (array) ($decoded['guidelines'] ?? []),
                    fn ($guideline) => is_array($guideline) || is_object($guideline)
                ))
            );

            $numbers = array_column($guidelines, 'number');

            return [
                'guidelines' => $guidelines,
                'feedback' => is_string($decoded['feedback'] ?? null) ? $decoded['feedback'] : null,
                'guideline_ids' => self::normaliseIds($numbers),
                'malformed_numbers' => self::malformedNumbers($numbers),
                'parse_method' => 'json',
            ];
        }

        $scraped = self::scrapeNumbers($text);

        return [
            'guidelines' => [],
            'feedback' => null,
            'guideline_ids' => self::normaliseIds($scraped),
            'malformed_numbers' => self::malformedNumbers($scraped),
            'parse_method' => $text === null || $text === '' ? 'empty' : 'regex',
        ];
    }

    /**
     * @return array<int, int>
     */
    public static function guidelineIds(?string $text): array
    {
        return self::parse($text)['guideline_ids'];
    }

    /**
     * Read ids straight out of an already-decoded guidelines list, for callers
     * holding a structured payload rather than model text.
     *
     * @param  array<int, mixed>  $guidelines
     * @return array{guideline_ids: array<int, int>, malformed_numbers: array<int, string>}
     */
    public static function fromGuidelines(array $guidelines): array
    {
        $numbers = array_column(
            array_map(
                fn ($guideline) => (array) $guideline,
                array_filter($guidelines, fn ($guideline) => is_array($guideline) || is_object($guideline))
            ),
            'number'
        );

        return [
            'guideline_ids' => self::normaliseIds($numbers),
            'malformed_numbers' => self::malformedNumbers($numbers),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function decode(?string $text): ?array
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        foreach (self::candidateJson($text) as $candidate) {
            $decoded = json_decode($candidate, true);

            if (is_array($decoded) && array_key_exists('guidelines', $decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private static function candidateJson(string $text): array
    {
        $candidates = [trim($text)];

        if (preg_match('/```(?:json)?\s*(.+?)```/s', $text, $matches)) {
            $candidates[] = trim($matches[1]);
        }

        $start = strpos($text, '{');
        $end = strrpos($text, '}');

        if ($start !== false && $end !== false && $end > $start) {
            $candidates[] = substr($text, $start, $end - $start + 1);
        }

        return array_values(array_unique($candidates));
    }

    /**
     * The guidelines in JSON that was cut off, in the order they appear: each
     * `"number"` with the `"assessment"` that follows it, if the cut came later.
     * Fields are written in schema order, so an assessment always belongs to
     * the number before it.
     *
     * @return array<int, array{number: string, assessment: string|null}>
     */
    public static function scrapeGuidelines(?string $text): array
    {
        if ($text === null) {
            return [];
        }

        preg_match_all('/"(number|assessment)"\s*:\s*(?:"([^"]*)"|(\d+)(?=\s*[,}\n]))/', $text, $matches, PREG_SET_ORDER);

        $guidelines = [];

        foreach ($matches as $match) {
            $value = trim(($match[3] ?? '') !== '' ? $match[3] : $match[2]);

            if ($match[1] === 'number') {
                $guidelines[] = ['number' => $value, 'assessment' => null];
            } elseif ($guidelines !== []) {
                $guidelines[array_key_last($guidelines)]['assessment'] = $value;
            }
        }

        return $guidelines;
    }

    /**
     * @return array<int, string>
     */
    private static function scrapeNumbers(?string $text): array
    {
        if ($text === null) {
            return [];
        }

        // Any complete value counts, so a malformed number such as `"33 and 34"`
        // is still seen; a value cut off before it ends is not.
        preg_match_all('/"number"\s*:\s*(?:"([^"]*)"|(\d+)(?=\s*[,}\n]))/', $text, $matches, PREG_SET_ORDER);

        return array_map(fn (array $match) => trim(($match[2] ?? '') !== '' ? $match[2] : $match[1]), $matches);
    }

    /**
     * The recommender sometimes packs several guidelines into one `number`, e.g.
     * `"35, 36"`. Production drops those outright — `isValidGuidelineId()` rejects
     * a non-numeric value — so the intended ids are recovered here and the
     * malformed values reported separately, rather than losing the signal.
     *
     * @param  array<int, mixed>  $numbers
     * @return array<int, int>
     */
    private static function normaliseIds(array $numbers): array
    {
        $normalised = [];

        foreach ($numbers as $number) {
            if (! is_scalar($number)) {
                continue;
            }

            preg_match_all('/\d+/', (string) $number, $matches);

            foreach ($matches[0] as $digits) {
                if ((int) $digits > 0) {
                    $normalised[] = (int) $digits;
                }
            }
        }

        sort($normalised);

        return array_values(array_unique($normalised));
    }

    /**
     * Values production would refuse to store as a guideline id.
     *
     * @param  array<int, mixed>  $numbers
     * @return array<int, string>
     */
    private static function malformedNumbers(array $numbers): array
    {
        $malformed = [];

        foreach ($numbers as $number) {
            if (is_scalar($number) && ! is_numeric($number) && preg_match('/\d/', (string) $number)) {
                $malformed[] = (string) $number;
            }
        }

        return array_values(array_unique($malformed));
    }
}
