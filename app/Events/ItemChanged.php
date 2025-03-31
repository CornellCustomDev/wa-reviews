<?php

namespace App\Events;

use App\Models\Item;

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

    protected function getProjectId(): int
    {
        return $this->item->issue->project_id;
    }
}
