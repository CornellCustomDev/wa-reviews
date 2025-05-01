<?php

require_once __DIR__.'/../vendor/autoload.php';

use LarAgent\Drivers\OpenAi\OpenAiCompatible;
use LarAgent\Drivers\OpenAi\OpenAiDriver;
use LarAgent\History\InMemoryChatHistory;
use LarAgent\LarAgent;
use LarAgent\Message;
use LarAgent\Tool;

// Configuration options
$config = [
    'model' => 'gpt-4o-mini',
];

// Setup
$yourApiKey = include __DIR__.'/../openai-api-key.php';
$driver = new OpenAiDriver(['api_key' => $yourApiKey]);
// $driver = new OpenAiCompatible(['api_key' => $yourApiKey]);

// Create a chat history
$chatKey = 'streaming-tool-test';
$chatHistory = new InMemoryChatHistory($chatKey);

// Setup the agent
$agent = LarAgent::setup($driver, $chatHistory, [
    'model' => $config['model'],
]);

// Create a weather tool function
function get_current_weather($location)
{
    // Simulate API call
    $temp = ($location == 'Boston') ? 15 : 22;
    $condition = ($location == 'Boston') ? 'partly cloudy' : 'sunny';

    echo "\n🔧 Weather tool called for: $location\n";
    echo "🔧 Result: $temp, $condition\n\n";

    return "The current weather in $location is $temp and $condition.";
}

// Create and configure the tool
$weatherTool = Tool::create('get_current_weather', 'Get the current weather in a location');
$weatherTool->addProperty('location', 'string', 'The city and state, e.g. San Francisco, CA')
    ->setRequired('location')
    ->setCallback('get_current_weather');

// Register the tool and force tool usage
$agent->setTools([$weatherTool]);

// Set the user message
$userMessage = Message::user('I need the current weather. Use the get_current_weather tool with location=Boston and location=Los Angeles. Return the results in celsius.');

// Set instructions
$agent->withInstructions('You are a weather assistant. ALWAYS use the get_current_weather tool when asked about weather. NEVER make up weather information.');
$agent->withMessage($userMessage);

// Function to handle streaming chunks
function handleStreamingChunk($chunk)
{
    try {
        // Skip tool call messages (LarAgent will handle it)
        if ($chunk instanceof \LarAgent\Messages\ToolCallMessage) {
            return;
        }
        // Only output what's new since last output
        echo $chunk->getLastChunk();
    } catch (\Throwable $e) {
        echo "\n⚠️ Error in streaming callback: ".$e->getMessage()."\n";
        echo $e->getTraceAsString()."\n";
    }
}

// Run the test
echo "🚀 Starting LarAgent Streaming Tool Test\n";
echo '- Model: '.$config['model']."\n\n";
echo '💬 User Message: '.$userMessage->getContent()."\n\n";
echo "🤖 Assistant Response:\n";

try {
    // Use streaming with callback
    $stream = $agent->runStreamed('handleStreamingChunk');

    // Consume the stream to ensure it completes
    foreach ($stream as $_) {
        // echo $_->getLastChunk();
        // The callback handles the output
    }

    echo "\n\n✅ Streaming completed\n";
} catch (\Throwable $e) {
    echo "\n\n❌ Error: ".$e->getMessage()."\n";
    echo "Stack trace:\n".$e->getTraceAsString()."\n";
}
