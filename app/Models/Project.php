<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'site_url',
        'description',
        'siteimprove_url',
        'siteimprove_id',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    public function scopes(): HasMany
    {
        return $this->hasMany(Scope::class);
    }

    public static function getTeamProjects(User $user): Collection
    {
        if ($user->isAdministrator()) {
            return Project::all();
        }
        return Project::query()->whereIn('team_id', $user->teams->pluck('id'))->get();
    }
}
