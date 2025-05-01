<?php

// config for Maestroerror/LarAgent
return [

    /**
     * Default driver to use, binded in service provider
     * with \LarAgent\Core\Contracts\LlmDriver interface
     */
    'default_driver' => \LarAgent\Drivers\OpenAi\OpenAiCompatible::class,

    /**
     * Default chat history to use, binded in service provider
     * with \LarAgent\Core\Contracts\ChatHistory interface
     */
    'default_chat_history' => \LarAgent\History\InMemoryChatHistory::class,

    /**
     * Always keep provider named 'default'
     * You can add more providers in array
     * by copying the 'default' provider
     * and changing the name and values
     */
    'providers' => [
        'default' => [
            'label' => 'openai',
            'api_key' => env('CORNELL_AI_GATEWAY_API_KEY'),
            'api_url' => env('CORNELL_AI_GATEWAY_BASE_URL', 'https://api.ai.it.cornell.edu'),
            'model' => env('CORNELL_AI_GATEWAY_MODEL', 'openai.gpt-4.1-mini'),
            'default_context_window' => 100000,
            'default_max_completion_tokens' => 10000,
            'default_temperature' => 1,
        ],

        'local' => [
            'label' => 'local',
            'model' => env('LOCAL_AI_MODEL', 'llama3.2:latest'),
            'driver' => \LarAgent\Drivers\OpenAi\OpenAiCompatible::class,
            'api_key' => 'ollama',
            'api_url' => env('LOCAL_AI_BASE_URL', 'http://host.docker.internal:11434/v1'),
            'default_context_window' => 100000,
            'default_max_completion_tokens' => 10000,
            'default_temperature' => 1,
        ],
    ],
];
