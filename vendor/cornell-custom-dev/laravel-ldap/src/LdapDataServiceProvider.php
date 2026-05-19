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
