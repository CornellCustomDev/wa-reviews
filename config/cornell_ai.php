<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Service
    |--------------------------------------------------------------------------
    |
    | Options:
    | - \App\Services\CornellAI\ApiGatewayChatService::class
    | - \App\Services\CornellAI\AzureChatService::class
    | - \App\Services\CornellAI\OpenAIChatService::class
    |
    */
    'ai_service' => env('AI_SERVICE', \App\Services\CornellAI\ApiGatewayChatService::class),
    /*
    |--------------------------------------------------------------------------
    | Cornell AI API Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Base URL and API key for Cornell AI API Gateway.
    |
    */

    'api_gateway' => [
        'base_url' => env('CORNELL_AI_GATEWAY_BASE_URL', 'https://api.ai.it.cornell.edu'),
        'api_key' => env('CORNELL_AI_GATEWAY_API_KEY'),
        'model' => env('CORNELL_AI_GATEWAY_MODEL', 'openai.gpt-4.1-mini'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Azure OpenAI Configuration
    |--------------------------------------------------------------------------
    |
    | API endpoint and keys for Azure OpenAI.
    |
    */

    'azure_openai' => [
        'endpoint' => env('AZURE_OPENAI_ENDPOINT', 'https://wa-review-ai.openai.azure.com/'),
        'api_key' => env('AZURE_OPENAI_API_KEY'),
        'api_version' => env('AZURE_OPENAI_API_VERSION', '2024-05-01-preview'),
        'model' => env('AZURE_OPENAI_MODEL', 'gpt-4.1-mini'),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Base URL and API key for OpenAI.
    |
    */

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4.1-mini'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Chat Profiles
    |--------------------------------------------------------------------------
    |
    | Predefined chat profiles for different use cases.
    |
    */
    'profiles' => [
        \App\Enums\ChatProfile::Reasoning->value => [
            'model' => env('AI_MODEL_REASONING', 'openai.o4-mini'),
        ],
        \App\Enums\ChatProfile::Chat->value => [
            'model' => env('AI_MODEL_CHAT', 'openai.gpt-4.1-mini'),
        ],
        \App\Enums\ChatProfile::Task->value => [
            'model' => env('AI_MODEL_TASK', 'openai.gpt-4o-mini'),
        ],
        \App\Enums\ChatProfile::Default->value => [
            'model' => env('AI_MODEL_DEFAULT', 'openai.gpt-4.1-mini'),
        ],
    ]
];
