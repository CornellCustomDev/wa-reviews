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
     * Autodiscovery namespaces for Agent classes.
     * Used by `agent:chat` to locate agents.
     */
    'namespaces' => [
        'App\\AiAgents\\',
        'App\\Agents\\',
    ],

    /**
     * Always keep provider named 'default'
     * You can add more providers in array
     * by copying the 'default' provider
     * and changing the name and values
     *
     * You can remove any other providers
     * which your project doesn't need
     */
    'providers' => [
        'default'=> [
            'label' => 'openai',
            'driver' => \LarAgent\Drivers\OpenAi\OpenAiCompatible::class,
            'api_key' => env('CORNELL_AI_GATEWAY_API_KEY'),
            'api_url' => env('CORNELL_AI_GATEWAY_BASE_URL', 'https://api.ai.it.cornell.edu'),
            'model' => env('CORNELL_AI_GATEWAY_MODEL', 'openai.gpt-4.1-mini'),
            'default_context_window' => 100000,
            'default_max_completion_tokens' => 10000,
            'default_temperature' => 1,
        ],

        'local' => [
            'label' => 'local',
            'model' => env('LOCAL_AI_MODEL', 'llama3.2:3b'),
            'driver' => \LarAgent\Drivers\OpenAi\OpenAiCompatible::class,
            'api_key' => 'ollama',
            'api_url' => env('LOCAL_AI_BASE_URL', 'http://host.docker.internal:11434/v1'),
            'default_context_window' => 100000,
            'default_max_completion_tokens' => 10000,
            'default_temperature' => 1,
        ],

        'openai-direct' => [
            'label' => 'openai-direct',
            'driver' => \App\AiAgents\LlmDrivers\OpenAiCompatibleStrict::class,
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4.1-mini'),
            'default_context_window' => 100000,
            'default_max_completion_tokens' => 10000,
            'default_temperature' => 1,
        ],

        'anthropic-direct' => [
            'label' => 'anthropic-direct',
            'driver' => \LarAgent\Drivers\OpenAi\OpenAiCompatible::class,
            'api_key' => env('ANTHROPIC_API_KEY'),
            'api_url' => env('ANTHROPIC_API_URL', 'https://api.anthropic.com/v1'),
            'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-0'),
            'default_context_window' => 20000,
            'default_max_completion_tokens' => 8000,
            'default_temperature' => 1,
        ],

        /**
         * Fallback provider to use when any provider fails.
         */
        //'fallback_provider' => 'default',
    ],
];
