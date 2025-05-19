<?php

namespace App\AiAgents;

use App\AiAgents\Tools\AnalyzeIssueTool;
use App\AiAgents\Tools\FetchGuidelinesDocumentTool;
use App\AiAgents\Tools\FetchGuidelinesListTool;
use App\AiAgents\Tools\FetchGuidelinesTool;
use App\AiAgents\Tools\ScratchPadTool;
use App\Enums\ChatProfile;
use Illuminate\Support\Collection;
use LarAgent\Agent;
use LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;
use LarAgent\Messages\SystemMessage;
use Throwable;

class ChatAgent extends Agent
{
    protected $history = 'file';

    protected $tools = [
        FetchGuidelinesTool::class,
        FetchGuidelinesListTool::class,
        FetchGuidelinesDocumentTool::class,
        ScratchPadTool::class,
        AnalyzeIssueTool::class,
    ];

    public function __construct(string $key)
    {
        $this->provider = config('cornell_ai.laragent_profile');
        $this->model = config('cornell_ai.profiles')[ChatProfile::Chat->value]['model'];

        parent::__construct($key);
    }

    protected function buildSessionId(): string
    {
        return sprintf(
            '%s_%s_%s',
            class_basename($this),
            auth()->id(),
            $this->getChatKey()
        );
    }

    /**
     * @throws Throwable
     */
    public function instructions(): string
    {
        return view('ai-agents.Chat.instructions', [
            'tools' => $this->getTools(),
        ])->render();
    }

    protected function beforeSaveHistory(ChatHistoryInterface $history): true
    {
        // Remove the instructions from the history
        if ($history[0] instanceof SystemMessage) {
            $history->truncateOldMessages(1);
        }

        return true;
    }

    public function getChats(): Collection
    {
        return collect();
    }

    public function getChatMessages($chatKey): array
    {
        $this->setChatSessionId($chatKey);
        $this->setupChatHistory();

        return $this->chatHistory()->getMessages();
    }

    public function updateChatName(): void
    {
        // No-op for now
    }
}
