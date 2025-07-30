<?php

namespace App\Ai\Prism\Agents;

use App\Ai\Prism\Tools\FetchGuidelinesListTool;
use App\Ai\Prism\Tools\FetchGuidelinesTool;
use App\Ai\Prism\Tools\FetchScopePageContentTool;
use App\Ai\Prism\Tools\ScratchPadTool;
use App\Enums\Agents;
use App\Enums\ChatProfile;
use App\Models\Agent;
use App\Models\Scope;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Prism\Prism\Providers\OpenAI\Maps\MessageMap;
use Prism\Prism\Text\PendingRequest as PendingTextRequest;
use Prism\Prism\Text\Response;
use Throwable;

class GuidelineRecommenderAgent extends PendingTextRequest
{
    protected Agent $agent;

    public function __construct(
        private readonly Scope $scope
    ) {
        $this->agent = Agent::findAgent(Agents::GuidelineRecommender);

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

        return view('ai-agents.GuidelineRecommender.instructions', [
            'guidelinesList' => $guidelinesListTool(),
            'scopeContext' => GuidelinesAnalyzerService::getScopeContext($this->scope),
        ])->render();
    }

    public function getAgent(): Agent
    {
        return $this->agent;
    }

    public function mapMessages(Response $response): array
    {
        return (new MessageMap($response->responseMessages->toArray(), []))();
    }
}
