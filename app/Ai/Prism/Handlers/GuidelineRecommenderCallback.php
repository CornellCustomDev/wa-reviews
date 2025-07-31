<?php
namespace App\Ai\Prism\Handlers;

use App\Models\ChatHistory;
use Closure;

class GuidelineRecommenderCallback
{
    public function __construct(
        private readonly Closure $callback
    ) {}

    public function handle(array $guidelines, ?ChatHistory $chatHistory): void
    {
        ($this->callback)($guidelines, $chatHistory);
    }
}
