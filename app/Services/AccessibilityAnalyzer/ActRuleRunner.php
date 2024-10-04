<?php

namespace App\Services\AccessibilityAnalyzer;

use App\Models\ActRule;
use ReflectionClass;

abstract class ActRuleRunner extends RuleRunner
{
    private ActRule $actRule;

    public function __construct(
    ) {
        $id = substr((new ReflectionClass($this))->getShortName(), -6);
        $this->actRule = ActRule::find($id);
    }

    public function getRuleType(): string
    {
        return 'act';
    }

    public function getRuleId(): string
    {
        return $this->actRule->id;
    }

    public function getName(): string
    {
        return $this->actRule->name;
    }

    public function getMachineName(): string
    {
        return $this->actRule->machine_name;
    }

    public function getGuidelineIds(): array
    {
        return $this->actRule->guidelines()->pluck('guidelines.id')->toArray();
    }

    public function getModel(): ActRule
    {
        return $this->actRule;
    }

    public function getPassingTestCases(): array
    {
        return $this->actRule->getPassingTestCases();
    }

    public function getFailingTestCases(): array
    {
        return $this->actRule->getFailingTestCases();
    }

    public function getInapplicableTestCases(): array
    {
        return $this->actRule->getInapplicableTestCases();
    }

    public function getAiPromptDescription(): string
    {
        // Extract rule details
        $ruleDescription = $this->actRule->getLongDescription();

        // Prepare passing test cases
        $passingCasesText = "\n\nPassing Test Cases:\n";
        $passingTestCases = $this->actRule->getPassingTestCases();
        foreach ($passingTestCases as $testCase) {
            $passingCasesText .= "Description: " . $testCase['description'] . "\n";
            $passingCasesText .= "HTML:\n" . $testCase['html'] . "\n\n";
        }
        $ruleDescription .= $passingCasesText;

        // Prepare failing test cases
        $failingCasesText = "\n\nFailing Test Cases:\n";
        $failingTestCases = $this->actRule->getFailingTestCases();
        foreach ($failingTestCases as $testCase) {
            $failingCasesText .= "Description: " . $testCase['description'] . "\n";
            $failingCasesText .= "HTML:\n" . $testCase['html'] . "\n\n";
        }
        $ruleDescription .= $failingCasesText;

        return $ruleDescription;
    }
}
