<?php

namespace App\Providers;

use App\Services\AzureOpenAI\ChatService;
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
                prompt: strval(config('azure_openai.prompt')),
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
