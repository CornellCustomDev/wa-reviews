<?php

namespace Tests\Feature;

use App\Services\AccessibilityContentParser\AccessibilityContentParserService;
use App\Services\AccessibilityContentParser\ActRules\ActRuleBase;

class ActRulesTest extends FeatureTestCase
{
    /**
     * @dataProvider rules
     */
    public function testPassedExamples(ActRuleBase $actRule)
    {
        $rule = $actRule->getRule();
        $cases = $rule->getPassedTestCases();

        foreach ($cases as $case) {
            $ruleInfo = $case['name'].': '.$case['html'];
            $this->assertTrue($actRule->doesRuleApply($case['html']), $ruleInfo);
        }
    }

    /**
     * @dataProvider rules
     */
    public function testFailedExamples(ActRuleBase $actRule)
    {
        $rule = $actRule->getRule();
        $cases = $rule->getFailedTestCases();

        foreach ($cases as $case) {
            $ruleInfo = $case['name'].': '.$case['html'];
            $this->assertTrue($actRule->doesRuleApply($case['html']), $ruleInfo);
        }
    }

    /**
     * @dataProvider rules
     */
    public function testInapplicableExamples(ActRuleBase $actRule)
    {
        $rule = $actRule->getRule();
        $cases = $rule->getInapplicableTestCases();

        foreach ($cases as $case) {
            $ruleInfo = $case['name'].': '.$case['html'];
            $this->assertFalse($actRule->doesRuleApply($case['html']), $ruleInfo);
        }
    }

    public static function rules(): array
    {
        $rules = AccessibilityContentParserService::getAllRules();

        return $rules->map(fn ($rule) => [$rule])->toArray();
    }
}
