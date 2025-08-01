<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ChatHistory extends Model
{
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $fillable = [
        'ulid',
        'agent_id',
        'user_id',
        'context_type',
        'context_id',
        'messages',
        'name',
    ];

    protected $casts = [
        'messages' => 'array',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function context(): MorphTo
    {
        return $this->morphTo();
    }
}
