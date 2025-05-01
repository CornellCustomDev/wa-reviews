<?php

namespace LarAgent\Drivers\OpenAi;

use OpenAI;

class OpenAiDriver extends BaseOpenAiDriver
{
    protected mixed $client;

    public function __construct(array $provider = [])
    {
        parent::__construct($provider);
        $this->client = $provider['api_key'] ? OpenAI::client($provider['api_key']) : null;
    }
}
