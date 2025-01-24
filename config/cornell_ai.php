<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Service
    |--------------------------------------------------------------------------
    |
    | Options:
    | - \App\Services\CornellAI\APIGateway\OpenAIChatService::class
    | - \App\Services\CornellAI\AzureOpenAI\ChatService::class
    |
    */
    'ai_service' => \App\Services\CornellAI\APIGateway\OpenAIChatService::class,

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
        'model' => env('CORNELL_AI_GATEWAY_MODEL', 'openai.gpt-4o-mini'),
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
        'model' => env('AZURE_OPENAI_MODEL', 'gpt-4o-mini'),
    ],
];
