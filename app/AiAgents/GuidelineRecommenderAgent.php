<?php

namespace App\AiAgents;

use App\AiAgents\Tools\FetchGuidelinesListTool;
use App\AiAgents\Tools\FetchGuidelinesTool;
use App\AiAgents\Tools\FetchIssuePageContentTool;
use App\AiAgents\Tools\FetchScopePageContentTool;
use App\Enums\ChatProfile;
use App\Models\Scope;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use LarAgent\Agent;
use LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;
use LarAgent\Messages\SystemMessage;
use Throwable;

class GuidelineRecommenderAgent extends Agent
{
    protected Scope $scope;

    protected $history = 'file';

    protected $tools = [
        FetchGuidelinesTool::class,
        FetchScopePageContentTool::class,
//        FetchIssuePageContentTool::class,
    ];

    public function __construct(Scope $scope, string $key)
    {
        $this->provider = config('cornell_ai.laragent_profile');
        $this->model = config('cornell_ai.profiles')[ChatProfile::Chat->value]['model'];

        $this->scope = $scope;

        parent::__construct($key);
    }

    protected function buildSessionId(): string
    {
        return sprintf(
            '%s_%s_%s',
            class_basename($this),
            $this->scope->id,
            $this->getChatKey()
        );
    }

    /**
     * @throws Throwable
     */
    public function instructions(): string
    {
        return view('ai-agents.GuidelineRecommender.instructions', [
            'tools' => $this->getTools(),
            'guidelinesList' => json_encode(FetchGuidelinesListTool::call(), JSON_PRETTY_PRINT),
            'scopeContext' => GuidelinesAnalyzerService::getScopeContext($this->scope),
        ])->render();
    }

    public function structuredOutput(): array
    {
        return [
            'name' => 'guideline_recommender_response',
            'schema' => GuidelinesAnalyzerService::getRecommendedGuidelinesSchema(),
            'strict' => true,
        ];
    }

    protected function beforeSaveHistory(ChatHistoryInterface $history): true
    {
        // Remove the instructions from the history
        if ($history[0] instanceof SystemMessage) {
            $history->truncateOldMessages(1);
        }

        return true;
    }
}
