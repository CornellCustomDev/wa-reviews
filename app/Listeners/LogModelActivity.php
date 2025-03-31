<?php

namespace App\Listeners;

use App\Events\IssueChanged;
use App\Events\ItemChanged;
use App\Events\ProjectChanged;
use App\Models\Activity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogModelActivity
{
    public function handle(ProjectChanged|IssueChanged|ItemChanged $modelChangedEvent): void
    {
        $actor = $modelChangedEvent->actor;

        Activity::create([
            'actor_id'     => $actor ? $actor->id : null,
            'actor_type'   => $actor ? get_class($actor) : 'system',
            'project_id'   => $modelChangedEvent->project_id,
            'subject_id'   => $modelChangedEvent->model->id,
            'subject_type' => get_class($modelChangedEvent->model),
            'action'       => $modelChangedEvent->action,
            'delta'        => $modelChangedEvent->delta,
            'created_at'   => $modelChangedEvent->timestamp,
        ]);
    }
}
