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
    protected function getLoggedInTestUser(string|array $role, ?string $teamName = null, ?User $user = null): User
    {
        $user = $user ?: $this->getTestUser(Arr::wrap($role), $teamName);
        $this->actingAs($user);
        return $user;
    }

    /**
     * Returns a test user, with an optional list of roles and team.
     */
    protected function getTestUser(array $roles = null, ?string $teamName = null): User
    {
        $user = User::factory()->create();
        $team = $this->getTestTeam($teamName);
        $team->users()->syncWithoutDetaching([$user->id]);
        if ($roles) {
            $user->syncRoles($roles, $team);
        } else {
            $user->syncRoles([Roles::TeamAdmin], $team);
        }
        return $user;
    }

    /**
     * Returns a test team. If a name is provided, use it; otherwise, generates a random name.
     *
     * @param string|null $name
     * @return Team
     */
    protected function getTestTeam(?string $name = null): Team
    {
        return Team::factory()->create([
            'name' => $name ?? 'Test Team ' . uniqid(),
        ]);
    }
}
