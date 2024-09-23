<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ActRule extends Model
{
    public $guarded = [];

    public $casts = [
        'id' => 'string',
        'metadata' => 'array',
    ];

    public function getDescription(): string
    {
        return $this->metadata['description'];
    }

    public function getMapping(): string
    {
        $metadata = $this->metadata;
        $rule_type = $metadata['rule_type'];
        $accessibility_requirements = $metadata['accessibility_requirements'];

        return $rule_type . ' - ' . join(', ', array_keys($accessibility_requirements));
    }

    public function getCriteria(): Collection
    {
        $accessibility_requirements = collect($this->metadata['accessibility_requirements']);
        // $key is of the format `wcag21:1.1.1`

        $refs = $accessibility_requirements->keys()->map(fn ($key) => explode(':', $key)[1]);

        return Criterion::whereIn('number', $refs)->get();
    }

    public function getTestCases(): array
    {
        $examples = [];
        $cases = $this->test_cases;
        while (preg_match('/^####\s*(.*?)\s*$(.*?)```html\n(.*?)\n```/ms', $cases, $matches)) {
            $name = $matches[1];
            $description = trim($matches[2]);
            $html = $matches[3];
            $cases = str_replace($matches[0], '', $cases);
            // get the first word of the name
            $type = explode(' ', $name)[0];
            // match to the test case type
            $testType = match($type) {
                'Passed' => 'passed',
                'Failed' => 'failed',
                'Inapplicable' => 'inapplicable',
                default => 'unknown',
            };
            $examples[$testType][] = [
                'name' => $name,
                'description' => $description,
                'html' => $html,
            ];
        }

        return $examples;
    }
}
