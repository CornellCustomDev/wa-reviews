<?php

namespace App\Providers;

use App\Models\User;
use App\Services\CornellAI\APIGateway\OpenAIChatService as APIGatewayChatService;
use App\Services\CornellAI\AzureOpenAI\ChatService as AzureOpenAIChatService;
use App\Services\CornellAI\OpenAIChatService;
use App\Services\SiteImprove\SiteimproveService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            abstract: OpenAIChatService::class,
            concrete: match(config('cornell_ai.ai_service')) {
                APIGatewayChatService::class => fn() => new APIGatewayChatService(
                    baseUrl: strval(config('cornell_ai.api_gateway.base_url')),
                    apiKey: strval(config('cornell_ai.api_gateway.api_key')),
                    model: strval(config('cornell_ai.api_gateway.model')),
                ),
                AzureOpenAIChatService::class => fn() => new AzureOpenAIChatService(
                    endpoint: strval(config('cornell_ai.azure_openai.endpoint')),
                    apiKey: strval(config('cornell_ai.azure_openai.api_key')),
                    apiVersion: strval(config('cornell_ai.azure_openai.api_version')),
                    model: strval(config('cornell_ai.azure_openai.model')),
                ),
            },
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
        Gate::after(fn (User $u) => $u->isAdministrator() ? true : null);
    }
}
