<?php

namespace App\Providers;

use App\Services\AccessibilityContentParser\ActRules\DataObjects\RuleSynth;
use App\Services\AzureOpenAI\ChatService;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::propertySynthesizer(RuleSynth::class);
    }
}
