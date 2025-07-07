<?php

namespace App\Ai\Prism\Agents;

use App\Ai\Prism\Tools\FetchGuidelinesListTool;
use App\Ai\Prism\Tools\FetchGuidelinesTool;
use App\Ai\Prism\Tools\FetchScopePageContentTool;
use App\Enums\ChatProfile;
use App\Models\Scope;
use Prism\Prism\Text\PendingRequest as PendingTextRequest;
use Throwable;

class GuidelineRecommenderAgent extends PendingTextRequest
{
    public function __construct(
        private readonly Scope $scope
    ) {
        $this->using(
            provider: config('cornell_ai.prism_provider'),
            model: config('cornell_ai.profiles')[ChatProfile::Chat->value]['model'],
        );

        $this->withTools(array_filter([
            new FetchGuidelinesTool(),
            new FetchGuidelinesListTool(),
            $scope->pageHasBeenRetrieved() ? new FetchScopePageContentTool() : null,
        ]))->withMaxSteps(5);

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

        return view('ai-agents.GuidelineRecommender.instructions', [
            'tools' => $this->tools,
            'guidelinesList' => json_encode($guidelinesListTool(), JSON_PRETTY_PRINT),
            'scopeContext' => $this->scope,
        ])->render();
    }
}
