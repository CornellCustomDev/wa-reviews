<?php

namespace App\Services\GuidelinesAnalyzer;

use App\Models\Agent;
use App\Services\CornellAI\ChatServiceFactoryInterface;

interface GuidelinesAnalyzerServiceInterface
{
    public function __construct(ChatServiceFactoryInterface $chatServiceFactory);

    public static function getAgent(): Agent;

    public function getTools(): array;
}
