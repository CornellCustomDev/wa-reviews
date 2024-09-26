<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ActRule extends Model
{
    public $guarded = [];

    public $casts = [
        'id' => 'string',
        'metadata' => 'array',
        'test_cases' => 'array',
    ];

    public function getMachineName(): string
    {
        return $this->machine_name;
    }

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
        // TODO Confirm the key format is correct
        $accessibility_requirements = collect($this->metadata['accessibility_requirements']);
        // $key is of the format `wcag21:1.1.1`

        $refs = $accessibility_requirements->keys()->map(fn ($key) => explode(':', $key)[1]);

        return Criterion::whereIn('number', $refs)->get();
    }

    public function getLongDescription(): string
    {
        // Split the markdown at "## Test Cases", removing everything after that
        return explode('## Test Cases', $this->markdown)[0];
    }

    public function getPassingTestCases(): array
    {
        return $this->test_cases['passed'];
    }

    public function getFailingTestCases(): array
    {
        return $this->test_cases['failed'];
    }

    public function getInapplicableTestCases(): array
    {
        return $this->test_cases['inapplicable'];
    }

    public function getRuleRunnerName(): string
    {
        return Str::studly($this->machine_name);
    }
}
