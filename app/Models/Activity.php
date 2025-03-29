<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'actor_id',
        'actor_type',
        'subject_id',
        'subject_type',
        'action',
        'delta'
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
     * Subject of the activity (e.g., an Issue or Item).
     */
    public function subject()
    {
        return $this->morphTo();
    }
}
