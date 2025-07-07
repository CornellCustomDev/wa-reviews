<?php

namespace App\AiAgents\Tools;

use LarAgent\Core\Contracts\Tool as ToolInterface;

interface BaseToolInterface extends ToolInterface
{
    public function useStrict(): bool;

    /**
     * Entry point executed by LarAgent.
     * Handles validation, enum coercion and error wrapping.
     */
    public function execute(array $input): mixed;
}
