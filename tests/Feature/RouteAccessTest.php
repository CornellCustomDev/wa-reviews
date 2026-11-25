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
        Config::set('app.env', 'production');
        config(['cu-auth.remote_user_override' => null]);
        $ssoLogin = route('cu-auth.sso-login', ['redirect_url' => route('projects')]);

        // CUAuth redirects to login
        $this->get(route('projects'))->assertRedirect($ssoLogin);
    }

    #[Test]
    public function livewire_update_route_requires_authentication(): void
    {
        config(['cu-auth.remote_user_override' => null]);

        $this->post('/livewire/update')->assertForbidden();
    }
}
