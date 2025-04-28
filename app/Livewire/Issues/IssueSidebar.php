<?php

namespace App\Livewire\Issues;

use App\Models\Issue;
use Livewire\Component;

class IssueSidebar extends Component
{
    public Issue $issue;

    public bool $showChat = false;
    public bool $showAnalyzer = false;
    public bool $showGuideline = false;
    public bool $showDebug = false;

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
