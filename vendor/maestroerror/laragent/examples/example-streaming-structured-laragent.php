<?php

require_once __DIR__.'/../vendor/autoload.php';

use LarAgent\Drivers\OpenAi\OpenAiCompatible;
use LarAgent\Drivers\OpenAi\OpenAiDriver;
use LarAgent\History\InMemoryChatHistory;
use LarAgent\LarAgent;
use LarAgent\Message;
use LarAgent\Messages\StreamedAssistantMessage;

// Configuration options
$config = [
    'model' => 'gpt-4o-mini',
];

// Setup
$yourApiKey = include __DIR__.'/../openai-api-key.php';
$driver = new OpenAiDriver(['api_key' => $yourApiKey]);
// Uncomment to test with OpenAI compatible driver
// $driver = new OpenAiCompatible(['api_key' => $yourApiKey, 'api_url' => 'https://api.openai.com/v1']);

// Define a JSON schema for structured output
$responseSchema = [
    'name' => 'Profile',
    'schema' => [
        'type' => 'object',
        'properties' => [
            'name' => ['type' => 'string'],
            'age' => ['type' => 'integer'],
            'interests' => ['type' => 'array', 'items' => ['type' => 'string']],
            'summary' => ['type' => 'string'],
        ],
        'required' => ['name', 'age', 'interests', 'summary'],
        'additionalProperties' => false,
    ],
    'strict' => true,
];

// Set the response schema on the driver
$driver->setResponseSchema($responseSchema);

// Create a chat history
$chatKey = 'streaming-structured-test';
$chatHistory = new InMemoryChatHistory($chatKey);

// Setup the agent
$agent = LarAgent::setup($driver, $chatHistory, [
    'model' => $config['model'],
]);

// Set the user message
$userMessage = Message::user('Create a profile for a fictional person named John who is 35 years old and loves hiking, coding, and photography.');

// Set instructions
$agent->withInstructions('You are a profile generator. Generate profiles in the requested JSON format.');
$agent->withMessage($userMessage);

// Run the test
echo "🚀 Starting LarAgent Streaming Structured Output Test\n";
echo '- Model: '.$config['model']."\n";
echo '- Structured output enabled: '.($driver->structuredOutputEnabled() ? 'Yes' : 'No')."\n\n";
echo '💬 User Message: '.$userMessage->getContent()."\n\n";
echo "🤖 Assistant Response (JSON):\n";

try {
    // Use streaming with callback
    $stream = $agent->runStreamed();

    // Consume the stream to ensure it completes
    foreach ($stream as $_) {
        if ($_ instanceof StreamedAssistantMessage) {
            echo $_->getLastChunk();
        } else {
            // The last message is array in case of structured output
            echo "\n\n";
            echo 'Structured Output:';
            echo "\n";
            var_dump($_);
        }
    }

    echo "\n\n✅ Streaming completed\n";
} catch (\Throwable $e) {
    echo "\n\n❌ Error: ".$e->getMessage()."\n";
    echo "Stack trace:\n".$e->getTraceAsString()."\n";
}
