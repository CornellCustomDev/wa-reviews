<?php

namespace App\Events;

use App\Models\Project;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ProjectChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $project_id;

    public function __construct(
        public Project $model,
        public string $action,
        public ?array $delta = null,
        public mixed $actor = null,
        public Carbon $timestamp = new Carbon()
    ) {
        $this->project_id = $model->id;
        $this->delta = $delta ?? collect($model->getChanges())->except(['updated_at'])->toArray();
        $this->actor = $actor ?? auth()->user();
        $this->timestamp = now();
    }
}
