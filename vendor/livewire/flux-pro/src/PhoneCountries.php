<?php

namespace FluxPro;

class PhoneCountries
{
    public static function all(): array
    {
        $locale = app()->getLocale();
        static $countries = [];

        if (isset($countries[$locale])) return $countries[$locale];

        $data = json_decode(file_get_contents(__DIR__.'/../dist/phone-countries.json'), true);

        $data = collect($data)->map(function ($country) use ($locale) {
            $label = class_exists(\Locale::class)
                ? \Locale::getDisplayRegion('und_'.strtoupper($country['iso2']), $locale)
                : null;

            $country['label'] = $label ?: $country['name'];

            return $country;
        })->all();

        $collator = class_exists(\Collator::class) ? new \Collator($locale) : null;

        usort($data, fn ($a, $b) => $collator
            ? $collator->compare($a['label'], $b['label'])
            : strcasecmp($a['label'], $b['label']));

        return $countries[$locale] = $data;
    }
}
