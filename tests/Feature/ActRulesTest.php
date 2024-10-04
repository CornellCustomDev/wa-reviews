<?php

namespace Tests\Feature;

use App\Services\AccessibilityAnalyzer\RuleRunner;

class ActRulesTest extends FeatureTestCase
{
    /**
     * @dataProvider ruleRunners
     */
    public function testPassedExamples(string $className)
    {
        $fullyQualifiedClassName = 'App\Services\AccessibilityAnalyzer\ActRules\\'.$className;
        /** @var RuleRunner $ruleRunner */
        $ruleRunner = new $fullyQualifiedClassName;

        $cases = $ruleRunner->getPassingTestCases();

        foreach ($cases as $case) {
            $ruleInfo = $case['name'].': '.$case['html'];
            $this->assertTrue($ruleRunner->doesRuleApply($case['html']), $ruleInfo);
        }
    }

    /**
     * @dataProvider ruleRunners
     */
    public function testFailedExamples(string $className)
    {
        $fullyQualifiedClassName = 'App\Services\AccessibilityAnalyzer\ActRules\\'.$className;
        /** @var RuleRunner $ruleRunner */
        $ruleRunner = new $fullyQualifiedClassName;

        $cases = $ruleRunner->getFailingTestCases();

        foreach ($cases as $case) {
            $ruleInfo = $case['name'].': '.$case['html'];
            $this->assertTrue($ruleRunner->doesRuleApply($case['html']), $ruleInfo);
        }
    }

    /**
     * @dataProvider ruleRunners
     */
    public function testInapplicableExamples(string $className)
    {
        $fullyQualifiedClassName = 'App\Services\AccessibilityAnalyzer\ActRules\\'.$className;
        /** @var RuleRunner $ruleRunner */
        $ruleRunner = new $fullyQualifiedClassName;

        $cases = $ruleRunner->getInapplicableTestCases();

        foreach ($cases as $case) {
            $ruleInfo = $case['name'].': '.$case['html'];
            $this->assertFalse($ruleRunner->doesRuleApply($case['html']), $ruleInfo);
        }
    }

    protected static function ruleRunners(): array
    {
        return collect(scandir(__DIR__.'/../../app/Services/AccessibilityAnalyzer/ActRules'))
            ->filter(fn($file) => pathinfo($file, PATHINFO_EXTENSION) === 'php')
            ->mapWithKeys(function ($file) {
                $name = pathinfo($file, PATHINFO_FILENAME);
                return [$name => [$name]];
            })
            ->toArray();
    }
}
