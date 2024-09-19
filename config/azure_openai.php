<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Azure OpenAI Configuration
    |--------------------------------------------------------------------------
    |
    | API endpoint and keys for Azure OpenAI.
    |
    */

    'endpoint' => env('AZURE_OPENAI_ENDPOINT', 'https://wa-review-ai.openai.azure.com/'),
    'api_key' => env('AZURE_OPENAI_API_KEY'),
    'api_version' => env('AZURE_OPENAI_API_VERSION', '2024-05-01-preview'),
    'model' => env('AZURE_OPENAI_MODEL', 'gpt-4o-mini'),
];
