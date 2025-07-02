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
            'schema' => [
                'description' => 'Return a "guidelines" array (if applicable warnings or failures are found) and a "feedback" string',
                'type' => 'object',
                'properties' => [
                    'guidelines' => [
                        'type' => ['array', 'null'],
                        'description' => 'Array of applicable guideline objects when accessibility barriers are found. Null if none are applicable.',
                        'items' => GuidelinesAnalyzerService::getItemsSchema(),
                    ],
                    'feedback' => [
                        'type' => ['string'],
                        'description' => 'A brief explanation or summary, or a clarification request if more information is needed.',
                    ],
                ],
                'additionalProperties' => false,
                'required' => ['guidelines', 'feedback'],
            ],
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
