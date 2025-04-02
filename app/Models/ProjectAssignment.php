<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectAssignment extends Model
{
    use HasTimestamps;
    use SoftDeletes;

    protected $fillable = ['project_id', 'user_id'];

    public static function boot()
    {
        parent::boot();

        // Enforce only one active record per project
        static::saving(function (self $assignment) {
            if (ProjectAssignment::where('project_id', $assignment->project_id)->exists()) {
                throw new \Exception("Duplicate entry for project_id=$assignment->project_id.");
            }
        });
    }

    public function project(): hasOne
    {
        return $this->hasOne(
            related: Project::class,
            foreignKey: 'assignment_id',
            localKey: 'id'
        );
    }

    public function reviewer(): hasOne
    {
        return $this->hasOne(
            related: User::class,
            foreignKey: 'id',
            localKey: 'user_id'
        );
    }
}
