<?php

namespace App\Livewire\AI;

use App\Services\AccessibilityContentParser\AccessibilityContentParserService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AnalyzePage extends Component
{
    public string $pageUrl;
    public string $pageContent;
    public array $rules;

    public function analyze()
    {
        $parser = new AccessibilityContentParserService();

        $cacheKey = 'page_body_' . md5($this->pageUrl);
        $body = cache()->remember($cacheKey, 3600, function () use ($parser) {
            $html = $parser->retrieveHtml($this->pageUrl);
            $crawler = $parser->parseDom($html);
            return $crawler->filter('body')->html();
        });

        $this->pageContent = $body;

        $this->rules = $parser->getApplicableRules($body);
    }

    public function render()
    {
        return view('livewire.ai.analyze-page');
    }
}
