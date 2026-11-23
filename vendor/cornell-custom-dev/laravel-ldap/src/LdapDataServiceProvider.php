<?php

namespace CornellCustomDev\LaravelStarterKit\Ldap;

use Illuminate\Support\ServiceProvider;

class LdapDataServiceProvider extends ServiceProvider
{
    const string INSTALL_CONFIG_TAG = 'ldap-config';

    public function register(): void
    {
        $this->mergeConfigFrom(
            path: __DIR__.'/../config/ldap.php',
            key: 'ldap',
        );

        $this->app->singleton(
            abstract: LdapService::class,
            concrete: fn () => new LdapService(
                server: config('ldap.server'),
                user: config('ldap.user') ? 'uid='.config('ldap.user').',ou=Directory Administrators,o=Cornell University,c=US' : '',
                pass: config('ldap.pass'),
                baseDn: config('ldap.base_dn'),
            ),
        );
        $this->app->alias(LdapService::class, 'ldap.cornell');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/ldap.php' => config_path('ldap.php'),
            ], 'starterkit:'.self::INSTALL_CONFIG_TAG);
        }
    }
}
