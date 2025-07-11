<?php

namespace App\Events;

use App\Models\Guideline;

class GuidelineChanged extends AbstractModelChanged
{
    public function __construct(
        private readonly Guideline $guideline,
        string $action,
        ?array $delta = null,
        mixed $actor = null,
    ) {
        parent::__construct($guideline, $action, $delta, $actor);
    }

    public function getContextType(): string
    {
        return Guideline::class;
    }

    protected function getContextId(): int
    {
        return $this->guideline->id;
    }
}
