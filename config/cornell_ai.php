<?php

use App\Enums\ChatProfile;
use App\Services\CornellAI\ApiGatewayChatService;

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
    'ai_service' => env('AI_SERVICE', ApiGatewayChatService::class),
    'laragent_profile' => env('LARAGENT_PROFILE', 'default'),
    'prism_provider' => env('PRISM_PROVIDER', 'cornell'),

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
    | Page Content Analysis Limit
    |--------------------------------------------------------------------------
    |
    | Stored page content larger than this many characters is refused before a
    | model is called, rather than failing mid-stream with a context-window
    | error from the gateway. Measured in characters because no tokenizer is
    | needed to check it; this markup runs about 2.8 characters per token, so
    | 250,000 characters is roughly 90,000 tokens and leaves ample headroom
    | inside a 128,000-token window for the prompt and the response.
    |
    | See docs/oversized-page-content.md.
    |
    */
    'page_content_char_limit' => env('AI_PAGE_CONTENT_CHAR_LIMIT', 250_000),

    /*
    |--------------------------------------------------------------------------
    | Chat Profiles
    |--------------------------------------------------------------------------
    |
    | Predefined chat profiles for different use cases.
    |
    */
    'profiles' => [
        ChatProfile::Reasoning->value => [
            'model' => env('AI_MODEL_REASONING', 'openai.o4-mini'),
        ],
        ChatProfile::Chat->value => [
            'model' => env('AI_MODEL_CHAT', 'openai.gpt-4.1-mini'),
            // Optional: minimal, low, medium or high. Unset uses the model's own
            // default. A model that does not reason may reject it.
            'reasoning_effort' => env('AI_REASONING_EFFORT_CHAT'),
        ],
        ChatProfile::Task->value => [
            'model' => env('AI_MODEL_TASK', 'gemini-3.5-flash-lite'),
        ],
        ChatProfile::Default->value => [
            'model' => env('AI_MODEL_DEFAULT', 'openai.gpt-4.1-mini'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cornell AI API Models
    |--------------------------------------------------------------------------
    |
    | Named models available through the Cornell AI API's LiteLLM gateway.
    | `lando artisan ai:eval` suggests these when asking which model to use,
    | in addition to the models already seen in past eval runs and the model
    | currently configured for production.
    |
    */
    'api_models' => [
        // Legacy
        'openai.gpt-4o-mini',    // $0.165
        'openai.gpt-4.1-mini',   // $0.40
        'openai.o4-mini',        // $1.10
        // Available
        'openai.gpt-5.4-nano',   // $0.20
        'openai.gpt-5.4-mini',   // $0.75
        'gpt-6-luna',            // $0.10 [2x if more than 272K tokens]
        'gpt-6.1-sol',           // $2.00 [2x if more than 272K tokens]
        'gemini-3.8-flash',      // $0.75 [on 50% of until Dec 31, 2026]
        'gemini-3.5-flash-lite', // $0.30 [faster, may need a little prompt tuning]
    ],
];
