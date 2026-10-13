<?php

namespace App\Livewire\Ai;

use App\Models\Scope;

class ScopeChat extends ChatBot
{
    public Scope $scope;

    /**
     * The whole page is inlined into the prompt below, so an oversized page has
     * to be refused before the request is sent.
     */
    public function sendChatMessage(): void
    {
        if ($this->scope->pageContentExceedsAnalysisLimit()) {
            $this->feedback = $this->scope->pageContentAnalysisRefusal();

            return;
        }

        parent::sendChatMessage();
    }

    public function getChatPrompt(): string
    {
        $context = $this->scope->getPageContent();

        return <<<PROMPT
As an expert in web accessibility guidelines and web development, your task is to answer
questions a user might have about the web page content below. If you need more information,
please ask the user for clarification.

Page Url: {$this->scope->url}

Page Content:
```html
{$context}
```
PROMPT;
    }
}
