<?php

namespace App\Services\GoogleApi;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class GoogleApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            path: __DIR__.'/googleapi.php',
            key: 'googleapi',
        );

        $this->app->singleton(GoogleService::class, function (Application $app) {
            return new GoogleService();
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/googleapi.php' => config_path('googleapi.php'),
            ], 'googleapi-config');
        }

        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
