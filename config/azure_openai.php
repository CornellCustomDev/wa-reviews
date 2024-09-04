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

    'search_endpoint' => env('AZURE_OPENAI_SEARCH_ENDPOINT', 'https://wa-review-ai-search.search.windows.net'),
    'search_key' => env('AZURE_OPENAI_SEARCH_KEY'),
    'index_name' => env('AZURE_OPENAI_SEARCH_INDEX', 'wa-guidelines'),

    'role_information' => env('AZURE_OPENAI_ROLE_INFORMATION', 'Assistant is an AI chatbot that helps users identify web accessibility guidelines that apply to an issue. It will briefly identify the applicable guidelines in a formatted list, including the guideline number, heading, and a brief description and then ask the user which guidelines to review. Once the user has identified the guidelines to review, it will output a list that includes: brief description of how the guideline applies to the issue, brief remediation recommendations, and very brief testing recommendations.'),

];
