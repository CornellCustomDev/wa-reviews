<?php

namespace App\Models;

use App\Enums\Agents;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $fillable = [
        'name',
    ];

    public static function findAgent(Agents $name): ?static
    {
        return Agent::firstWhere('name', $name->value);
    }
}
