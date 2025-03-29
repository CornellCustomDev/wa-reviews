<?php

namespace App\Events;

use App\Models\Item;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ItemChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Model $model;
    public string $action;
    public ?array $delta = null;
    public mixed $actor;
    public Carbon $timestamp;

    public function __construct(Item $item, string $action, array $delta = [], $actor = null)
    {
        $this->model = $item;
        $this->action = $action;
        $this->delta = $delta;
        $this->actor = $actor ?? auth()->user();
        $this->timestamp = now();
    }
}
