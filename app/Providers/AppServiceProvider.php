<?php

namespace App\Providers;

use App\Services\AzureOpenAI\ChatService;
use App\Services\SiteImprove\SiteimproveService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            abstract: ChatService::class,
            concrete: fn() => new ChatService(
                endpoint: strval(config('azure_openai.endpoint')),
                apiKey: strval(config('azure_openai.api_key')),
                apiVersion: strval(config('azure_openai.api_version')),
                model: strval(config('azure_openai.model')),
            ),
        );
        $this->app->singleton(
            abstract: SiteimproveService::class,
            concrete: fn() => new SiteimproveService(
                apiKey: strval(config('siteimprove.api_key')),
            ),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
