<?php

namespace App\Providers;

use App\Models\User;
use App\Services\CornellAI\ApiGatewayChatService;
use App\Services\CornellAI\AzureChatService;
use App\Services\CornellAI\ChatServiceFactory;
use App\Services\CornellAI\ChatServiceFactoryInterface;
use App\Services\CornellAI\OpenAIChatService;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerServiceInterface;
use App\Services\SiteImprove\SiteimproveService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use OpenAI;

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
                ApiGatewayChatService::class => fn() => new APIGatewayChatService(
                    baseUrl: strval(config('cornell_ai.api_gateway.base_url')),
                    apiKey: strval(config('cornell_ai.api_gateway.api_key')),
                    model: strval(config('cornell_ai.api_gateway.model')),
                ),
                AzureChatService::class => fn() => new AzureChatService(
                    endpoint: strval(config('cornell_ai.azure_openai.endpoint')),
                    apiKey: strval(config('cornell_ai.azure_openai.api_key')),
                    apiVersion: strval(config('cornell_ai.azure_openai.api_version')),
                    model: strval(config('cornell_ai.azure_openai.model')),
                ),
                OpenAIChatService::class => fn() => new OpenAIChatService(
                    chat: OpenAI::client(strval(config('cornell_ai.openai.api_key')))->chat(),
                    model: strval(config('cornell_ai.openai.model')),
                ),
            },
        );
        $this->app->singleton(
            abstract: ChatServiceFactoryInterface::class,
            concrete: ChatServiceFactory::class,
        );
        $this->app->singleton(
            abstract: GuidelinesAnalyzerServiceInterface::class,
            concrete: GuidelinesAnalyzerService::class,
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
        // Allow administrative user access to all features
        Gate::before(fn (User $u) => $u->isAdministrator() ? true : null);
    }
}
