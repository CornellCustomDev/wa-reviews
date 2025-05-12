<?php

require_once __DIR__.'/../vendor/autoload.php';

use LarAgent\Drivers\OpenAi\OpenAiCompatible;
use LarAgent\LarAgent;
use LarAgent\Messages\StreamedAssistantMessage;
use LarAgent\Messages\ToolCallMessage;

// Example of using streaming only with Driver, instead of LarAgent class

// Initialize OpenAI driver with your API key
$yourApiKey = include __DIR__.'/../openai-api-key.php';
$driver = new OpenAiCompatible([
    'api_key' => $yourApiKey,
    'model' => 'gpt-4o-mini', // or any other model that supports streaming
]);

// Create a callback function to process each chunk
$streamCallback = function ($message) {
    if ($message instanceof StreamedAssistantMessage) {
        // For regular content messages
        echo $message->getLastChunk();

        // Check if this is the final message with usage information
        if ($message->isComplete() && $message->getUsage()) {
            echo 'Stream complete! Usage: '.json_encode($message->getUsage()).PHP_EOL;
        }
    } elseif ($message instanceof ToolCallMessage) {
        // For tool call messages
        echo 'Received tool call: '.json_encode($message->getToolCalls()).PHP_EOL;
    }
};

// Example messages
$messages = [
    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
    ['role' => 'user', 'content' => 'What is the biggest city by population?'],
];

// Use the streaming method directly from the driver
echo 'Starting stream...'.PHP_EOL;

$stream = $driver->sendMessageStreamed($messages, ['model' => 'gpt-4o-mini'], $streamCallback);

// Iterate through the stream (this is optional if you're using the callback)
foreach ($stream as $chunk) {
    // The callback will handle the output, but you could do additional processing here
}

echo 'Stream finished!'.PHP_EOL;
