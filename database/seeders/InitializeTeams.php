<?php

namespace Database\Seeders;

use App\Enums\Roles;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InitializeTeams extends Seeder
{
    public function run(): void
    {
        // Create the "Custom Development" team
        $team = Team::create([
            'name' => 'Custom Development',
        ]);

        // Add the team to all existing projects
        Project::query()->update(['team_id' => $team->id]);

        // Add all the users to the team
        User::all()->each(fn (User $user) => $team->users()->attach($user));

        // Give the first user a role of SiteAdmin
        $firstUser = User::first();
        $firstUser?->syncRoles([Roles::SiteAdmin]);
    }
}
