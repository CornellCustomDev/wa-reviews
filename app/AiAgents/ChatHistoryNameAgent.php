<?php

namespace App\AiAgents;

use App\Enums\ChatProfile;
use LarAgent\Agent;
use LarAgent\Core\Contracts\ChatHistory;
use LarAgent\Core\Contracts\Message;
use LarAgent\Core\Enums\Role;

class ChatHistoryNameAgent extends Agent
{
    protected $history = 'in_memory';

    protected $provider = 'default';

    protected $tools = [];

    public function __construct(string $key)
    {
        $this->model = config('cornell_ai.profiles')[ChatProfile::Task->value]['model'];

        parent::__construct($key);
    }

    public function instructions(): string
    {
        return <<<INSTRUCTIONS
You are an assistant that generates concise, specific titles for chat histories about web accessibility best practices.

## Naming Rules
1. Title length **3–5 words**.
2. Focus on the **most specific concept, HTML element, technique, guideline number, or WCAG success criterion** discussed.
   2a. Base the title primarily on USER messages; ignore assistant/system boilerplate.
3. **Avoid generic words of this context** such as "accessibility", "guidelines", "issues", "inquiry", "question", or
   "chat" unless they are part of an official title.
4. Start with an **action verb** when applicable (e.g., "Fix", "Explain", "Review").
5. If a WCAG criterion or guideline number appears in the messages, **include the number** (e.g., "WCAG 2.4.7 focus").
6. If the generated name is quite similar to the current name, keep the current name.
7. Do **not** reuse any name already present in **Current Chat History Names**.

Return **only** the title without quotes or extra text.

### Good Examples
- Explain alt text usage
- WCAG 1.4.3 contrast
- Fix keyboard trap
- Review Guideline 3
INSTRUCTIONS;
    }

    public function getChatName(ChatHistory $chatHistory, string $currentName, array $chatHistoryNames): string
    {
        $chatMessages = collect($chatHistory->getMessages());
        $filteredMessages = $chatMessages
            // Get the user messages
            ->filter(fn(Message $m) => in_array($m->getRole(), [Role::USER->value, Role::ASSISTANT->value]))
            ->toJson(JSON_PRETTY_PRINT);

        $currentChatHistoryNames = implode(', ', $chatHistoryNames);

        $context = <<<CONTEXT
## Current Name
$currentName

## Current Chat History Names
$currentChatHistoryNames

## Chat Messages
```json
$filteredMessages
```
CONTEXT;

        return $this->respond($context);
    }
}
