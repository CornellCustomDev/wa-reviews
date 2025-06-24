<?php

namespace App\AiAgents;

use App\AiAgents\Tools\FetchGuidelinesListTool;
use App\AiAgents\Tools\FetchGuidelinesTool;
use App\AiAgents\Tools\FetchScopePageContentTool;
use App\Enums\ChatProfile;
use App\Models\Scope;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use LarAgent\Agent;
use LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;
use LarAgent\Messages\SystemMessage;
use Throwable;

class ScopeAnalyzerAgent extends Agent
{
    protected Scope $scope;

    protected $history = 'file';

    protected $tools = [
        FetchGuidelinesTool::class,
        FetchGuidelinesListTool::class,
        FetchScopePageContentTool::class,
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
        return view('ai-agents.ScopeAnalyzer.instructions', [
            'tools' => $this->getTools(),
            'guidelinesList' => json_encode(FetchGuidelinesListTool::call(), JSON_PRETTY_PRINT),
        ])->render();
    }

    public function structuredOutput(): array
    {
        return [
            'name' => 'scope_analyzer_response',
            'schema' => [
                'description' => 'Return either an "issues" array (if applicable warnings or failures are found) or a "feedback" string (if no warning or failures apply or clarification is needed). Never return both.',
                'type' => 'object',
                'properties' => [
                    'issues' => [
                        'type' => ['array', 'null'],
                        'description' => 'Array of issues when accessibility barriers are found. Null if none are applicable.',
                        'items' => GuidelinesAnalyzerService::getIssueSchema(),
                    ],
                    'feedback' => [
                        'type' => ['string', 'null'],
                        'description' => 'A brief explanation if no guidelines apply, or a clarification request if more information is needed. Null if applicable guidelines are found.',
                    ],
                ],
                'additionalProperties' => false,
                'required' => ['issues', 'feedback'],
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
