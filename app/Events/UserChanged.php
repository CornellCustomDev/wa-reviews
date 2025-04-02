<?php

namespace App\Events;

use App\Models\Team;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserChanged extends AbstractModelChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        private readonly User $user,
        private readonly Team $team,
        string $action,
        ?array $delta = null,
        mixed $actor = null,
    ) {
        parent::__construct($user, $action, $delta, $actor);
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
