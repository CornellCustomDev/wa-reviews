<?php

namespace App\AiAgents;

use App\AiAgents\Tools\FetchGuidelines;
use App\AiAgents\Tools\FetchGuidelinesDocument;
use App\AiAgents\Tools\FetchGuidelinesList;
use App\Enums\ChatProfile;
use App\Models\Guideline;
use LarAgent\Agent;
use Throwable;

class GuidelineChatAgent extends Agent
{
    protected $history = 'file';


    protected $tools = [
        FetchGuidelines::class,
        FetchGuidelinesList::class,
        FetchGuidelinesDocument::class,
    ];

    private Guideline $guideline;

    public function __construct($key)
    {
        $this->provider = config('cornell_ai.laragent_profile');
        $this->model = config('cornell_ai.profiles')[ChatProfile::Chat->value]['model'];
        parent::__construct($key);
    }

    /**
     * @throws Throwable
     */
    public function instructions(): string
    {
        return view('ai-agents.GuidelineChat.instructions', [
            'tools' => $this->getTools(),
            'guideline' => $this->guideline ?? Guideline::find(1),
        ])->render();
    }

    public function setGuideline(Guideline $guideline): void
    {
        $this->guideline = $guideline;
    }
}
