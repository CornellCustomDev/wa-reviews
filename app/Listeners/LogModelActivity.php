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
    public function handle(ProjectChanged|IssueChanged|ItemChanged $event): void
    {
        $actor = $event->actor;

        Activity::create([
            'actor_id'     => $actor ? $actor->id : null,
            'actor_type'   => $actor ? get_class($actor) : 'system',
            'subject_id'   => $event->model->id,
            'subject_type' => get_class($event->model),
            'action'       => $event->action,
            'delta'        => $event->delta,
            'created_at'   => $event->timestamp,
        ]);
    }
}
