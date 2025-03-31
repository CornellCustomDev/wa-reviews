<?php

namespace App\Events;

use App\Models\Issue;

class IssueChanged extends AbstractModelChanged
{
    public function __construct(
        private readonly Issue $issue,
        string $action,
        ?array $delta = null,
        mixed $actor = null
    ) {
        parent::__construct($issue, $action, $delta, $actor);
    }

    protected function getProjectId(): int
    {
        return $this->issue->project_id;
    }
}
