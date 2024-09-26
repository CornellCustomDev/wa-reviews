<?php

namespace App\Livewire\Scopes;

use App\Models\ActRule;
use App\Models\Scope;
use App\Services\AccessibilityContentParser\AccessibilityContentParserService;
use App\Services\AzureOpenAI\ChatService;
use Livewire\Attributes\On;
use Livewire\Component;

class ShowScope extends Component
{
    public Scope $scope;
    public bool $entirePage = false;
    public array $suggestedRules = [];
    public string $response;

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
                'sidebar' => false,
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

        if ($this->entirePage) {
            $content = $body;
        } else {
            $sections = $parser->extractMajorSections($body);
            $content = $sections['main']['element'];
        }

        $rules = $parser->getApplicableRules($content);
        $nodes = $parser->getNodesWithApplicableRules($content);
        $this->suggestedRules = [];
        /** @var ActRule $rule */
        foreach ($rules as $rule) {
            $ruleNodes = $nodes[$rule->getMachineName()] ?? [];
            $this->suggestedRules[$rule->getMachineName()] = [
                'rule' => $rule,
                'criteria' => $rule->getCriteria(),
                'elements' => $ruleNodes['nodes'],
                'cssSelectors' => $ruleNodes['cssSelectors'],
                'results' => [],
            ];
        }
    }

    public function reviewElements(ActRule $rule, string $cssSelectors): void
    {
        $machineName = $rule->getMachineName();

        $parser = new AccessibilityContentParserService();
        $html = $parser->getPageContent($this->scope->url);
        $cssSelectorsList = explode(',', $cssSelectors);
        $nodes = $parser->findNodes($html, $cssSelectorsList);
        $prompt = $parser->getNodesPrompt($rule, $nodes, $html);

        $chat = ChatService::make();
        $chat->setPrompt($prompt);
        $chat->send();

        $json = $chat->getLastAiResponse();
        $response = json_decode($json);

        if (isset($response->elements)) {
            $this->suggestedRules[$machineName]['results'] = [];
            foreach ($response->elements as $element) {
                $this->suggestedRules[$machineName]['results'][] = [
                    'cssSelector' => $cssSelectorsList[$element->elementIndex],
                    'assessment' => $element->assessment,
                    'reasoning' => $element->reasoning,
                ];
            }
        }

        $this->response = json_encode($response, JSON_PRETTY_PRINT);
    }

    public function createIssues($ruleId): void
    {

        // Show a JS alert with Livewire
        $this->js('alert("Issues will be created for rule ' . $ruleId . ' once the feature is implemented.")');
    }
}
