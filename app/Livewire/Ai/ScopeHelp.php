<?php

namespace App\Livewire\Ai;

use App\Models\Scope;
use App\Models\ScopeRule;
use App\Services\AccessibilityAnalyzer\AccessibilityAnalyzerService;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ScopeHelp extends Component
{
    public Scope $scope;

    public bool $showChat = false;

    public ?string $response;

    #[Computed]
    public function rulesCount(): int
    {
        return AccessibilityAnalyzerService::getAllRuleRunnerClassNames()->count();
    }

    #[Computed]
    public function scopeGuidelineRules(): Collection
    {
        return $this->scope->rules()->where('guideline_id', $this->guideline->id)->get();
    }

    #[Computed]
    public function applicableRules(): Collection
    {
        return $this->scopeGuidelineRules()
            ->groupBy('rule_class')
            ->map(function ($group) {
                return [
                    'ruleRunner' => $group->first()->getRuleRunner(),
                    'count' => $group->count(),
                ];
            })
            ->values();
    }

    #[Computed]
    public function aiRuleResults(): Collection
    {
        return $this->scopeGuidelineRules()
            ->filter(fn (ScopeRule $rule) => $rule->hasAiAssessment());
    }

    #[On('scope-rules-updated')]
    public function loadGuidelineRules(): void
    {
        unset($this->scopeGuidelineRules);
        unset($this->applicableRules);
        unset($this->aiRuleResults);
    }

    public function reviewElements(string $ruleName, bool $bustCache = false): void
    {
        $scopeRules = $this->scope->rules()
            ->where('guideline_id', $this->guideline->id)
            ->where('rule_name', $ruleName)
            ->get();
        $ruleRunner = $scopeRules->first()->getRuleRunner();

        $cache_key = "review_elements_{$this->scope->id}_{$this->guideline->id}_$ruleName";
        if ($bustCache) {
            cache()->forget($cache_key);
        }
        $result = cache()->remember($cache_key, 300, function () use ($ruleRunner, $scopeRules) {
            $analyzerService = new AccessibilityAnalyzerService();
            $result = $analyzerService->reviewRuleWithAI($ruleRunner, $scopeRules, $this->scope->page_content);
            $this->response = $analyzerService->response;
            return $result;
        });

        if (count($result) > 0) {
            foreach ($result as $id => $element) {
                $this->scope->rules()
                    ->where('guideline_id', $this->guideline->id)
                    ->where('rule_name', $element['machine_name'])
                    ->where('css_selector', $element['css_selector'])
                    ->update([
                        'ai_assessment' => $element['assessment'],
                        'ai_description' => $element['description'],
                        'ai_reasoning' => $element['reasoning'],
                    ]);
            }
            unset($this->scopeGuidelineRules);
        }
    }

    public function createIssue(string $id): void
    {
        $scopeRule = ScopeRule::find($id);

        $issue = $this->scope->issues()->create([
            'project_id' => $this->scope->project_id,
            'target' => $scopeRule->ai_description,
            'cssSelector' => $scopeRule->css_selector,
            'description' => $scopeRule->ai_reasoning,
        ]);

        $guidelinesAnalyzer = app(GuidelinesAnalyzerService::class);
        $result = $guidelinesAnalyzer->analyzeIssue($issue);

        if (count($result) > 0) {
            $guidelinesAnalyzer->storeItems($issue, $result);
        }

        $this->dispatch('issues-updated');
    }
}
