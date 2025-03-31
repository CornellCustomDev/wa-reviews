<?php

namespace App\Listeners;

use App\Events\IssueChanged;
use App\Events\ItemChanged;
use App\Events\ProjectChanged;
use App\Events\TeamChanged;
use App\Events\UserChanged;
use App\Models\Activity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogModelActivity
{
    public function handle(ProjectChanged|IssueChanged|ItemChanged|TeamChanged|UserChanged $modelChangedEvent): void
    {
        $actor = $modelChangedEvent->actor;

        Activity::create([
            'actor_type'   => $actor ? get_class($actor) : 'system',
            'actor_id'     => $actor ? $actor->id : null,
            'context_type' => $modelChangedEvent->contextType,
            'context_id'   => $modelChangedEvent->contextId,
            'subject_type' => get_class($modelChangedEvent->model),
            'subject_id'   => $modelChangedEvent->model->id,
            'action'       => $modelChangedEvent->action,
            'delta'        => $modelChangedEvent->delta,
            'created_at'   => $modelChangedEvent->timestamp,
        ]);
    }
}
