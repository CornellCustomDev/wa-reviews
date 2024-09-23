<?php

namespace App\Livewire\Scopes;

use App\Models\Criterion;
use App\Models\Scope;
use App\Services\AccessibilityContentParser\AccessibilityContentParserService;
use App\Services\AccessibilityContentParser\ActRules\DataObjects\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class ShowScope extends Component
{
    public Scope $scope;
    public array $suggestedRules = [];

    #[On('issues-updated')]
    public function refreshScope(): void
    {
        $this->scope->refresh();
    }

    public function render()
    {
        $this->authorize('view', $this->scope);
        return view('livewire.scopes.show-scope')
            ->layout('components.layouts.app', [
                'breadcrumbs' => $this->getBreadcrumbs(),
            ]);
    }

    protected function getBreadcrumbs(): array
    {
        return [
            'Projects' => route('projects'),
            $this->scope->project->name => route('project.show', $this->scope->project),
            $this->scope->title => 'active'
        ];
    }

    public function analyze(): void
    {
        $parser = new AccessibilityContentParserService();
        $body = $parser->getPageContent($this->scope->url);
        $rules = $parser->getApplicableRules($body);
        /** @var Rule $rule */
        foreach ($rules as $rule) {
            $this->suggestedRules[] = [
                'rule' => $rule,
                'criteria' => Criterion::whereIn('number', $rule->getCriteria())->get(),
            ];
        }
    }

    public function createIssues($ruleId): void
    {

        // Show a JS alert with Livewire
        $this->js('alert("Issues will be created for rule ' . $ruleId . ' once the feature is implemented.")');
    }
}
