<?php

namespace App\Livewire\AI;

use App\Models\ActRule;
use App\Services\AccessibilityContentParser\AccessibilityContentParserService;
use App\Services\AccessibilityContentParser\ActRules\DataObjects\Rule;
use App\Services\AzureOpenAI\ChatService;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AnalyzePage extends Component
{
    public string $pageUrl = '';
    public bool $entirePage = false;
    public string $pageContent;
    public array $rules;
    public array $ruleNodes;
    public array $candidateIssues;
    public string $issuesResponse;
    public string $prompt;
    public array $sections;
    public array $selectedSection;

    public function analyze(): void
    {
        $parser = new AccessibilityContentParserService();
        $html = $parser->getPageContent($this->pageUrl);

        $this->pageContent = $html;
        $sections = $parser->extractMajorSections($html);
        $main = $sections['main']['element'];
        array_walk($sections, function (&$section) {
            unset($section['element']);
        });
        $this->sections = $sections;
        $this->selectedSection = $this->sections['main'];

        if ($this->entirePage) {
            $content = $html;
        } else {
            $content = $main;
        }

        $this->rules = $parser->getApplicableRules($content);
        $this->ruleNodes = $parser->getNodesWithApplicableRules($content);
        // $this->findIssues(head($this->rules));
    }

    public function reviewElementsWithAI(ActRule $rule, string $cssSelectors): void
    {
        // TODO - We need to use either the database or the yaml files, not both
        $rule = Rule::fromYaml($rule->getYaml());

        $parser = new AccessibilityContentParserService();
        $html = $parser->getPageContent($this->pageUrl);
        $nodes = $parser->findNodes($html, explode(',', $cssSelectors));
        $this->prompt = $parser->getNodesPrompt($rule, $nodes, '');

        $chat = ChatService::make();
        $chat->setPrompt($this->prompt.$html);
        $chat->send();
        $json = json_decode($chat->getLastAiResponse());
        $this->issuesResponse = '<pre>'.json_encode($json, JSON_PRETTY_PRINT).'</pre>';
    }

    public function render()
    {
        return view('livewire.ai.analyze-page');
    }
}
