<?php

namespace App\Services\CornellAI;

use App\Enums\ChatProfile;

interface ChatServiceFactoryInterface
{
    public function make(ChatProfile $profile): OpenAIChatService;
}
