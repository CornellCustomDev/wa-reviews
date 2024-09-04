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

    'endpoint' => env('AZURE_OPENAI_ENDPOINT'),
    'api_key' => env('AZURE_OPENAI_API_KEY'),
    'search_key' => env('AZURE_OPENAI_SEARCH_KEY'),

    'model' => env('AZURE_OPENAI_MODEL', 'gpt-4o-mini'),
    'api_version' => env('AZURE_OPENAI_API_VERSION', '2024-05-01-preview'),
    'config_file' => env('AZURE_OPENAI_CONFIG_FILE', 'azure-openai.json'),

];
