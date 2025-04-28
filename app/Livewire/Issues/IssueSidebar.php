<?php

namespace App\Livewire\Issues;

use App\Models\Issue;
use Livewire\Attributes\Url;
use Livewire\Component;

class IssueSidebar extends Component
{
    public Issue $issue;

    #[Url(as: 'chat', history: true)]
    public bool $showChat = false;
    #[Url(as: 'analyze', history: true)]
    public bool $showAnalyzer = false;
    #[Url(as: 'debug', history: true)]
    public bool $showDebug = false;
    public bool $showGuideline = false;

    public string $debug = '';

    public function clickChat(): void
    {
        $this->showChat = !$this->showChat;
        $this->showAnalyzer = false;
        $this->showDebug = false;
    }

    public function clickAnalyzer(): void
    {
        $this->showChat = false;
        $this->showAnalyzer = !$this->showAnalyzer;
        $this->showDebug = false;
    }

    public function clickDebug(): void
    {
        $this->showChat = false;
        $this->showAnalyzer = false;
        $this->showDebug = !$this->showDebug;
    }
}
