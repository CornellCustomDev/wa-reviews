<?php

namespace FluxPro;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class FluxProServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->alias(FluxProManager::class, 'flux-pro');

        $this->app->singleton(FluxProManager::class);

        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('FluxPro', \Flux\FluxPro::class);
    }

    public function boot(): void
    {
        $this->bootComponentPath();
    }

    public function bootComponentPath()
    {
        // view:cache compiles views at their real path, so keep ".." out of this one...
        Blade::anonymousComponentPath(dirname(__DIR__).'/stubs/resources/views/flux', 'flux');
    }
}
