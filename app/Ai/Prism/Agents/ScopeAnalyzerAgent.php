<?php

namespace App\Ai\Prism\Agents;

use App\Ai\Prism\Tools\FetchGuidelinesListTool;
use App\Ai\Prism\Tools\FetchGuidelinesTool;
use App\Ai\Prism\Tools\FetchScopePageContentTool;
use App\Ai\Prism\Tools\ScratchPadTool;
use App\Enums\ChatProfile;
use App\Models\Scope;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Prism\Prism\Text\PendingRequest;
use Throwable;

class ScopeAnalyzerAgent extends PendingRequest
{
    public function __construct(
        private readonly Scope $scope
    ) {
        $this->using(
            provider: config('cornell_ai.prism_provider'),
            model: config('cornell_ai.profiles')[ChatProfile::Chat->value]['model'],
        );

        $this->withTools(array_values(array_filter([
            new FetchGuidelinesTool(),
            $scope->pageHasBeenRetrieved() ? new FetchScopePageContentTool() : null,
            new ScratchPadTool(),
        ])))->withMaxSteps(10);

        $this->usingTemperature(0.2);

        $this->withSystemPrompt($this->instructions());
    }

    public static function for(Scope $scope): static
    {
        return new static($scope);
    }

    /**
     * @throws Throwable
     */
    protected function instructions(): string
    {
        $guidelinesListTool = new FetchGuidelinesListTool();

        return view('ai-agents.ScopeAnalyzer.instructions', [
            'guidelinesList' => $guidelinesListTool(),
            'scopeContext' => GuidelinesAnalyzerService::getScopeContext($this->scope),
        ])->render();
    }
}
