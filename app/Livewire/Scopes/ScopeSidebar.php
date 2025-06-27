<?php

namespace App\Livewire\Scopes;

use App\Models\Scope;
use Livewire\Attributes\Url;
use Livewire\Component;

class ScopeSidebar extends Component
{
    public Scope $scope;

    #[Url(as: 'content', history: true)]
    public bool $showContent = false;
    #[Url(as: 'chat', history: true)]
    public bool $showChat = false;
    #[Url(as: 'analyze', history: true)]
    public bool $showAnalyzer = false;

    public function clickContent(): void
    {
        $this->showContent = !$this->showContent;
        $this->showChat = false;
        $this->showAnalyzer = false;
    }

    public function clickChat(): void
    {
        $this->showContent = false;
        $this->showChat = !$this->showChat;
        $this->showAnalyzer = false;
    }

    public function clickAnalyzer(): void
    {
        $this->showContent = false;
        $this->showChat = false;
        $this->showAnalyzer = !$this->showAnalyzer;
    }
}
