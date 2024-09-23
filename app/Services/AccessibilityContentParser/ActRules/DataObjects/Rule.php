<?php

namespace App\Services\AccessibilityContentParser\ActRules\DataObjects;

class Rule
{
    public function __construct(
        public readonly string $machineName,
        public readonly string $id,
        public readonly string $name,
        public readonly array $metadata,
        public readonly string $applicability,
        public readonly string $assumptions,
        public readonly string $accessibility_support,
        public readonly string $background,
        public readonly array $test_cases,
        public readonly string $expectation,
    ) { }

    public static function fromYaml($yaml): Rule
    {
        return new Rule(
            machineName: $yaml['machine_name'],
            id: $yaml['id'],
            name: $yaml['name'],
            metadata: $yaml['metadata'],
            applicability: $yaml['applicability'],
            assumptions: $yaml['assumptions'],
            accessibility_support: $yaml['accessibility_support'],
            background: $yaml['background'],
            test_cases: $yaml['test_cases'],
            expectation: $yaml['expectation'],
        );
    }

    public function getCriteria(): array
    {
        $accessibility_requirements = collect($this->metadata['accessibility_requirements']);

        // $key is of the format `wcag21:1.1.1`
        return $accessibility_requirements->keys()
            ->map(fn ($requirement) => explode(':', $requirement)[1])
            ->toArray();
    }

    public function getPassedTestCases(): array
    {
        return $this->test_cases['passed'];
    }

    public function getFailedTestCases(): array
    {
        return $this->test_cases['failed'];
    }

    public function getInapplicableTestCases(): array
    {
        return $this->test_cases['inapplicable'];
    }
}
