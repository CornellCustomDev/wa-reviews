<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teams extends Model
{
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
