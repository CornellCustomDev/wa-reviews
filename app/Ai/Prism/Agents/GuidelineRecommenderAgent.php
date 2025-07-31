<?php

namespace App\Ai\Prism\Agents;

use App\Ai\Prism\Handlers\GuidelineRecommenderCallback;
use App\Ai\Prism\PrismHistory;
use App\Ai\Prism\PrismSchema;
use App\Ai\Prism\Tools\FetchGuidelinesListTool;
use App\Ai\Prism\Tools\FetchGuidelinesTool;
use App\Ai\Prism\Tools\FetchScopePageContentTool;
use App\Ai\Prism\Tools\ScratchPadTool;
use App\Enums\Agents;
use App\Enums\ChatProfile;
use App\Models\Agent;
use App\Models\Scope;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Illuminate\Database\Eloquent\Model;
use Prism\Prism\Providers\OpenAI\Maps\MessageMap;
use Prism\Prism\Text\PendingRequest as PendingTextRequest;
use Prism\Prism\Text\Response;
use Throwable;
use UnexpectedValueException;

class GuidelineRecommenderAgent extends PendingTextRequest
{
    use PrismSchema;
    use PrismHistory;

    protected Agent $agent;
    private ?Model $contextModel = null;
    private ?GuidelineRecommenderCallback $responseHandler = null;

    public function __construct(
        private readonly Scope $scope
    ) {
        $this->agent = Agent::findAgent(Agents::GuidelineRecommender);
        // Default the context model to the scope
        $this->withContextModel($scope);

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

    public function withContextModel(Model $contextModel): self
    {
        $this->contextModel = $contextModel;

        return $this;
    }

    public function withResponseHandler(GuidelineRecommenderCallback $callback): self
    {
        $this->responseHandler = $callback;

        return $this;
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

    public function storeResponse(?Response $response): void
    {
        if ($response) {
            $messageMap = new MessageMap($response->responseMessages->toArray(), []);
            $messages = $messageMap();
        } else {
            $messages = [];
        }

        $this->storeChatHistory(
            agent: $this->agent,
            contextModel: $this->contextModel,
            response: $response,
            messages: $messages,
        );
    }

    public function handleResponse(Response $prismResponse): ?string
    {
        try {
            $schema = $this->convertToPrismSchema(GuidelinesAnalyzerService::getRecommendedGuidelinesSchema());
            $response = $this->getStructuredResponse($prismResponse, $schema, $this->contextModel);
        } catch (UnexpectedValueException $e) {
            return $e->getMessage();
        }

        if ($response->guidelines && $this->responseHandler) {
            // Call the custom response handler if provided
            $this->responseHandler->handle($response->guidelines, $this->getChatHistory());
        }

        return $response->feedback;
    }
}
