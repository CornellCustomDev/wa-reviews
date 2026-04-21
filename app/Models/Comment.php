<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'commentable_type', 'commentable_id', 'body'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isEditableBy(User $user): bool
    {
        return $this->user_id === $user->id
            && $this->created_at->diffInMinutes(now()) <= 10;
    }

    public function isDeletableBy(User $user): bool
    {
        return $this->user_id === $user->id || $user->isAdministrator();
    }
}
