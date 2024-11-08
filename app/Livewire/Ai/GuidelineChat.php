<?php

namespace App\Livewire\Ai;

use App\Models\Guideline;

class GuidelineChat extends ChatBot
{
    public Guideline $guideline;

    public function getChatPrompt(): string
    {
        $context = $this->guideline->notes;
        return <<<PROMPT
As an expert in web accessibility guidelines and web development, your task is to answer
questions a user might have about the following guideline. If you need more information,
please ask the user for clarification.

--
{$this->guideline->name}

$context
PROMPT;
    }
}
