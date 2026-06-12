<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;

class RouteAccessTest extends FeatureTestCase
{
    #[Test]
    public function welcome_is_publicly_accessible(): void
    {
        $this->get(route('welcome'))->assertOk();
    }

    #[Test]
    public function login_redirects_unauthenticated_users(): void
    {
        $this->get(route('login'))->assertRedirect();
    }

    #[Test]
    public function projects_not_publicly_accessible(): void
    {
        config(['cu-auth.remote_user_override' => null]);

        // In non-prod, AppTesters middleware returns forbidden
        $this->get(route('projects'))->assertForbidden();

        // In prod, CUAuth redirects to login
        Config::set('app.env', 'production');
        $this->get(route('projects'))->assertRedirect();
    }

    #[Test]
    public function livewire_update_route_requires_authentication(): void
    {
        config(['cu-auth.remote_user_override' => null]);

        $response = $this->post('/livewire/update', ['payload' => []]);

        // Either 403 (forbidden) or 419 (CSRF token mismatch) is acceptable
        // The important thing is that the request is rejected
        $this->assertContains($response->getStatusCode(), [403, 419]);
    }
}
