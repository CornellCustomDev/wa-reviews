<?php

namespace App\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

abstract class AbstractModelChanged
{
    use Dispatchable, SerializesModels;

    public string $contextType;
    public int $contextId;
    public Carbon $timestamp;

    public function __construct(
        public Model $model,
        public string $action,
        public ?array $delta = null,
        public mixed $actor = null,
    ) {
        $this->contextType = $this->getContextType();
        $this->contextId = $this->getContextId();
        $this->delta = $delta ?? collect($model->getChanges())->except(['updated_at'])->toArray();
        $this->actor = $actor ?? auth()->user();
        $this->timestamp = now();
    }

    abstract protected function getContextType(): string;

    abstract protected function getContextId(): int;
}
