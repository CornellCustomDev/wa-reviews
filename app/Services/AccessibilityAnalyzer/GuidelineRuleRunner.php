<?php

namespace App\Services\AccessibilityAnalyzer;

use App\Models\Guideline;
use ReflectionClass;

abstract class GuidelineRuleRunner extends RuleRunner
{
    private Guideline $guideline;

    public function __construct()
    {
        $id = str_replace('Guideline', '', (new ReflectionClass($this))->getShortName());
        $this->guideline = Guideline::find($id);
    }

    public function getRuleType(): string
    {
        return 'guideline';
    }

    public function getRuleId(): string
    {
        return $this->guideline->id;
    }

    public function getMachineName(): string
    {
        return 'guideline-'.$this->guideline->id;
    }

    public function getName(): string
    {
        return $this->guideline->name;
    }

    public function getGuidelineIds(): array
    {
        return [$this->guideline->id];
    }

    public function getModel(): Guideline
    {
        return $this->guideline;
    }

    public function getPassingTestCases(): array
    {
        return [];
    }

    public function getFailingTestCases(): array
    {
        return [];
    }

    public function getInapplicableTestCases(): array
    {
        return [];
    }

    public function getAiPromptDescription(): string
    {
        return $this->guideline->description;
    }
}
