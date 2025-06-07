<?php

namespace App\AiAgents;

use App\AiAgents\Tools\ScratchPadTool;
use App\Enums\ChatProfile;
use App\Models\Issue;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Illuminate\Support\Facades\Storage;
use LarAgent\Agent;
use LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;
use LarAgent\Messages\SystemMessage;
use Throwable;

class GuidelinesAnalyzerAgent extends Agent
{
    protected $history = 'file';

    protected Issue $issue;

    public function __construct(Issue $issue, string $key)
    {
        $this->provider = config('cornell_ai.laragent_profile');
        $this->model = config('cornell_ai.profiles')[ChatProfile::Chat->value]['model'];

        $this->issue = $issue;

        parent::__construct($key);
    }

    protected function buildSessionId(): string
    {
        return sprintf(
            '%s_%s_%s',
            class_basename($this),
            $this->issue->id,
            $this->getChatKey()
        );
    }

    /**
     * @throws Throwable
     */
    public function instructions(): string
    {
        return view('ai-agents.GuidelinesAnalyzer.instructions', [
            'guidelinesDocument' => Storage::get('guidelines-list.md'),
        ])->render();
    }

    public function structuredOutput(): array
    {
        return [
            'name' => 'guidelines_analyzer_response',
            'schema' => [
                'description' => 'Return either a "guidelines" array (if applicable warnings or failures are found) or a "feedback" string (if no warning or failures apply or clarification is needed). Never return both.',
                'type' => 'object',
                'properties' => [
                    'guidelines' => [
                        'type' => ['array', 'null'],
                        'description' => 'Array of applicable guideline objects when accessibility barriers are found. Null if none are applicable.',
                        'items' => GuidelinesAnalyzerService::getItemsSchema(),
                    ],
                    'feedback' => [
                        'type' => ['string', 'null'],
                        'description' => 'A brief explanation if no guidelines apply, or a clarification request if more information is needed. Null if applicable guidelines are found.',
                    ],
                ],
                'additionalProperties' => false,
                'required' => ['guidelines', 'feedback'],
            ],
            'strict' => true,
        ];
    }

    public function getContext(): string
    {
        $context = '';
        if ($this->issue->scope?->pageHasBeenRetrieved()) {
            $context .= "# Context: Web page being analyzed\n\n"
                . "```html\n"
                . $this->issue->scope->getPageContent()
                . "\n```\n\n";
        }
        $context .= "# Context: Web accessibility issue\n\n"
            . GuidelinesAnalyzerService::getIssueContext($this->issue);

        return $context;
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
