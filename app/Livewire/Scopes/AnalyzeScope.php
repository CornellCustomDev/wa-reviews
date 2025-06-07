<?php

namespace App\Livewire\Scopes;

use App\Models\Scope;
use App\Services\AccessibilityAnalyzer\AccessibilityAnalyzerService;
use App\Services\AccessibilityAnalyzer\RuleRunner;
use Livewire\Attributes\On;
use Livewire\Component;

class AnalyzeScope extends Component
{
    public Scope $scope;
    public bool $entirePage = false;

    #[On('analyzePage')]
    public function analyze(): void
    {
        $parser = new AccessibilityAnalyzerService();
        $pageContent = $parser->getPageContent($this->scope->url, true);

        if ($this->entirePage) {
            $content = $pageContent;
        } else {
            $sections = $parser->extractMajorSections($pageContent);
            $content = $sections['main']['element'];
        }

        $this->updateScopeRules($parser, $content);
        $this->entirePage = false;
        $this->dispatch('scope-rules-updated');
    }

    private function updateScopeRules(AccessibilityAnalyzerService $parser, mixed $content): void
    {
        $nodes = $parser->getApplicableScopeRuleNodes($content);

        $scopeRules = [];
        /** @var RuleRunner $rule */
        foreach ($nodes as $node) {
            $ruleRunner = $node['ruleRunner'];
            $nodeInfo = $node['node_info'];

            $guidelineIds = $ruleRunner->getGuidelineIds();

            foreach ($guidelineIds as $guidelineId) {
                $scopeRules[] = [
                    'scope_id' => $this->scope->id,
                    'guideline_id' => $guidelineId,
                    'rule_class' => get_class($ruleRunner),
                    'rule_name' => $ruleRunner->getMachineName(),
                    'css_selector' => $nodeInfo['css_selector'],
                    'description' => $nodeInfo['description'] . ' (line ~' . $nodeInfo['line_number'] . ')',
                ];
            }
        }

        // TODO Do not delete, just update?
        $this->scope->rules()->delete();
        $this->scope->rules()->createMany($scopeRules);
    }

}
