<?php

namespace Tests\Feature;

use App\Services\AccessibilityAnalyzer\RuleRunner;
use PHPUnit\Framework\Attributes\DataProvider;

class ActRulesTest extends FeatureTestCase
{
    #[DataProvider('ruleRunners')]
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

    #[DataProvider('ruleRunners')]
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

    #[DataProvider('ruleRunners')]
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

    public static function ruleRunners(): array
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
