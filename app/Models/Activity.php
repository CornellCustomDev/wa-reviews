<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'actor_type',
        'actor_id',
        'context_type',
        'context_id',
        'subject_type',
        'subject_id',
        'action',
        'delta',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'delta' => 'array',
    ];

    /**
     * Generally a user, but could also be an agent or system.
     */
    public function actor()
    {
        return $this->morphTo();
    }

    /**
     * Context of the activity (e.g., a Project or Team).
     */
    public function context()
    {
        return $this->morphTo();
    }

    /**
     * Subject of the activity (e.g., an Issue or Item).
     */
    public function subject()
    {
        return $this->morphTo();
    }
}
