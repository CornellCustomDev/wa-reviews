<?php

namespace App\Events;

use App\Models\Issue;
use App\Models\Project;

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

    public function getContextType(): string
    {
        return Project::class;
    }

    protected function getContextId(): int
    {
        return $this->issue->project_id;
    }
}
