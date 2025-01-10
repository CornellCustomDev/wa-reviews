<?php

namespace App\Livewire\Ai;

use App\Models\Scope;

class ScopeChat extends ChatBot
{
    public Scope $scope;

    public function getChatPrompt(): string
    {
        $context = $this->scope->page_content;

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
