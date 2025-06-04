<?php

namespace Tests\Unit;

use App\Enums\ProjectStatus;
use App\Enums\Roles;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

trait TestDatabase
{
    use RefreshDatabase;

    protected array $connectionsToTransact = ['sqlite'];

    public function beforeRefreshingDatabase(): void
    {
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        config(['telescope.storage.database.connection' => 'sqlite']);
    }

    public static function setupTeam(
        User   $user,
        ?bool   $isTeamMember = false,
        ?Roles $role = null,
        ?Team  $projectTeam = null
    ): Team
    {
        $projectTeam ??= Team::factory()->create();
        if ($isTeamMember) {
            $team = $projectTeam;
        } else {
            $team = Team::factory()->create();
        }
        if (! is_null($isTeamMember)) {
            $team->users()->attach($user);
        }
        if ($role) {
            $user->addRole($role, $team);
        }

        return $projectTeam;
    }

    public static function setupProject(
        Team           $projectTeam,
        ?User          $user = null,
        bool           $isReviewer = false,
        bool           $hasReviewer = false,
        bool           $isReportViewer = false,
        ?ProjectStatus $status = null
    ): Project
    {
        $project = Project::factory()->create([
            'team_id' => $projectTeam->id,
            'status' => $status ?? ProjectStatus::NotStarted,
        ]);
        if ($isReviewer) {
            $project->assignment()->create([
                'user_id' => $user->id,
            ]);
        } elseif ($hasReviewer) {
            $project->assignment()->create([
                'user_id' => User::factory()->create()->id,
            ]);
        }
        if ($isReportViewer) {
            $project->reportViewers()->attach($user->id);
        }

        return $project;
    }
}
