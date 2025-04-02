<?php

namespace App\Events;

use App\Models\Team;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeamChanged extends AbstractModelChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        private readonly Team $team,
        Model $model,
        string $action,
        ?array $delta = null,
        mixed $actor = null,
    ) {
        parent::__construct($model, $action, $delta, $actor);
    }

    protected function getContextType(): string
    {
        return Team::class;
    }

    protected function getContextId(): int
    {
        return $this->team->id;
    }
}
