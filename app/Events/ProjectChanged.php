<?php

namespace App\Events;

use App\Models\Project;

class ProjectChanged extends AbstractModelChanged
{
    public function __construct(
        private readonly Project $project,
        string $action,
        ?array $delta = null,
        mixed $actor = null,
    ) {
        parent::__construct($project, $action, $delta, $actor);
    }

    public function getContextType(): string
    {
        return Project::class;
    }

    protected function getContextId(): int
    {
        return $this->project->id;
    }
}
