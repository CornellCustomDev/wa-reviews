<?php

namespace App\Services\AzureOpenAI;

class ChatConfig
{
    protected array $parameters;

    public function __construct(
        string $search_endpoint,
        string $search_key,
        string $index_name,
        string $role_information,

        float $temperature = 0.7,
        float $top_p = 0.95,
        int $max_tokens = 800,
        string $stop = null,
        bool $stream = false,
    )
    {
        $data_sources = [
            [
                'type' => 'azure_search',
                'parameters' => [
                    'endpoint' => $search_endpoint,
                    'index_name' => $index_name,
                    'semantic_configuration' => 'default',
                    'query_type' => 'semantic',
                    // 'fields_mapping' => [],
                    'in_scope' => true,
                    'role_information' => $role_information,
                    'filter' => null,
                    'strictness' => 3,
                    'top_n_documents' => 5,
                    'authentication' => [
                        'type' => 'api_key',
                        'key' => $search_key,
                    ]
                ],
            ]
        ];

        $this->parameters = [
            'data_sources' => $data_sources,
            'messages' => [[
                'role' => 'system',
                'content' => $role_information,
            ]],
            'temperature' => $temperature,
            'top_p' => $top_p,
            'max_tokens' => $max_tokens,
            'stop' => $stop,
            'stream' => $stream,
//            'frequency_penalty' => $frequency_penalty,
//            'presence_penalty' => $presence_penalty,
        ];
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
