<?php

namespace App\Services\AccessibilityContentParser\ActRules\DataObjects;

use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

class RuleSynth extends Synth
{
    public static string $key = 'rule';

    public static function match($target)
    {
        return $target instanceof Rule;
    }

    public function dehydrate(Rule $target): array
    {
        return [[
            'machineName' => $target->machineName,
            'id' => $target->id,
            'name' => $target->name,
            'metadata' => $target->metadata,
            'applicability' => $target->applicability,
            'assumptions' => $target->assumptions,
            'accessibility_support' => $target->accessibility_support,
            'background' => $target->background,
            'test_cases' => $target->test_cases,
            'expectation' => $target->expectation,
        ], []];
    }

    public function hydrate(array $value): Rule
    {
        return new Rule(
            machineName: $value['machineName'],
            id: $value['id'],
            name: $value['name'],
            metadata: $value['metadata'],
            applicability: $value['applicability'],
            assumptions: $value['assumptions'],
            accessibility_support: $value['accessibility_support'],
            background: $value['background'],
            test_cases: $value['test_cases'],
            expectation: $value['expectation'],
        );
    }
}
