<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\TestCase;

class FeatureTestCase extends TestCase
{
    use DatabaseTransactions;

    /**
     * Helper to create an authenticated user with a role assigned to a team.
     */
    protected function getLoggedInTestUser(string|array $role, ?Team $team = null, ?string $teamName = null, ?User $user = null): User
    {
        $user = $user ?: $this->makeTestUser(Arr::wrap($role), $team, $teamName);
        $this->actingAs($user);
        return $user;
    }

    /**
     * Returns a test user, with an optional list of roles and team.
     */
    protected function makeTestUser(?array $roles = [Roles::TeamAdmin], ?Team $team = null, ?string $teamName = null): User
    {
        $user = User::factory()->create();
        if (in_array(Roles::SiteAdmin, $roles)) {
            $user->addRole(Roles::SiteAdmin);
            $user->save();
        }

        // If the user is strictly a site admin, return without creating a team.
        if ($roles === [Roles::SiteAdmin] && !$team && !$teamName) {
            return $user;
        }

        $team = $team ?? $this->makeTestTeam($teamName);
        $team->users()->syncWithoutDetaching([$user->id]);
        $user->syncRoles($roles, $team);

        return $user;
    }

    /**
     * Returns a test team. If a name is provided, use it; otherwise, generates a random name.
     *
     * @param string|null $name
     * @return Team
     */
    protected function makeTestTeam(?string $name = null): Team
    {
        return Team::factory()->create([
            'name' => $name ?? 'Test Team ' . uniqid(),
        ]);
    }
}
