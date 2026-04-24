<?php

namespace App\Livewire\Scopes;

use App\Models\Scope;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
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
    #[Url(as: 'comments', history: true)]
    public bool $showComments = false;

    public function clickContent(): void
    {
        $this->showContent = !$this->showContent;
        $this->showChat = false;
        $this->showAnalyzer = false;
        $this->showComments = false;
    }

    public function clickChat(): void
    {
        $this->showContent = false;
        $this->showChat = !$this->showChat;
        $this->showAnalyzer = false;
        $this->showComments = false;
    }

    public function clickAnalyzer(): void
    {
        $this->showContent = false;
        $this->showChat = false;
        $this->showAnalyzer = !$this->showAnalyzer;
        $this->showComments = false;
    }

    public function clickComments(): void
    {
        $this->showContent = false;
        $this->showChat = false;
        $this->showAnalyzer = false;
        $this->showComments = !$this->showComments;
    }

    #[Computed]
    public function commentsCount(): int
    {
        return $this->scope->comments()->count();
    }

    #[On('comments-updated')]
    public function commentsUpdates(): void
    {
        unset($this->commentsCount);
    }
}
