<?php

namespace App\Events;

use App\Models\Item;
use App\Models\Project;

class ItemChanged extends AbstractModelChanged
{
    public function __construct(
        private readonly Item $item,
        string $action,
        ?array $delta = null,
        mixed $actor = null,
    ) {
        parent::__construct($item, $action, $delta, $actor);
    }

    public function getContextType(): string
    {
        return Project::class;
    }

    protected function getContextId(): int
    {
        return $this->item->issue->project_id;
    }
}
