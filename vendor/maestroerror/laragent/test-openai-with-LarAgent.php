<?php

require_once __DIR__.'/vendor/autoload.php';

use LarAgent\Drivers\OpenAi\OpenAiDriver;
use LarAgent\History\InMemoryChatHistory;
use LarAgent\LarAgent;
use LarAgent\Message;
use LarAgent\Messages\ToolCallMessage;
use LarAgent\Tool;

// Setup
$yourApiKey = include 'openai-api-key.php';
$driver = new OpenAiDriver(['api_key' => $yourApiKey]);
$chatKey = 'test-chat-history';
$chatHistory = new InMemoryChatHistory($chatKey);

$agent = LarAgent::setup($driver, $chatHistory, [
    'model' => 'gpt-4o-mini',
]);

// Sturctured output
$weatherInfoSchema = [
    'name' => 'weather_info',
    'schema' => [
        'type' => 'object',
        'properties' => [
            'locations' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'city' => ['type' => 'string'],
                        'weather' => ['type' => 'string'],
                    ],
                    'required' => ['city', 'weather'],
                    'additionalProperties' => false,
                ],
            ],
        ],
        'required' => ['locations'],
        'additionalProperties' => false,
    ],
    'strict' => true,
];

// Create tool

function get_current_weather($location, $unit = 'celsius')
{
    // Call the weather API
    return 'The weather in '.$location.' is 72 degrees '.$unit;
}

$toolName = 'get_current_weather';
$tool = Tool::create($toolName, 'Get the current weather in a given location');
$tool->addProperty('location', 'string', 'The city and state, e.g. San Francisco, CA')
    ->addProperty('unit', 'string', 'The unit of temperature', ['celsius', 'fahrenheit'])
    ->setRequired('location')
    ->setMetaData(['sent_at' => '2024-01-01'])
    // ->setCallback('get_current_weather')
    ->setCallback(function ($location, $unit = 'fahrenheit') {
        // "Call the weather API"
        return 'The weather in '.$location.' is 72 degrees '.$unit;
    });

$userMessage = Message::user('What\'s the weather like in Boston and Los Angeles? I prefer celsius');
$instuctions = 'You are weather assistant and always respond using celsius. If it provided as fahrenheit, convert it to celsius.';

$agent->setTools([$tool])->structured($weatherInfoSchema)
    ->withInstructions($instuctions)
    ->withMessage($userMessage);

$agent->afterToolExecution(function ($agent, $tool, &$result) {
    $sentAt = $tool->getMetaData()['sent_at'];
    if ($sentAt) {
        $result = $result.'. Specify the check date in answer. Checked at '.$sentAt;
    }
});

$agent->afterSend(function ($agent, $history, $message) use ($chatKey) {
    if ($message instanceof ToolCallMessage) {
        // echo $message->getCallId()."\n";
    } else {
        $usage = $message->getMetadata()['usage'];
        echo $usage->totalTokens.' Tokens used in chat: '.$chatKey."\n";
    }
});

$response = $agent->run();

// echo $response;
print_r($response);

/* Outputs:
Array
(
    [locations] => Array
        (
            [0] => Array
                (
                    [city] => Boston, MA
                    [weather] => The weather is 22 degrees Celsius.
                )

            [1] => Array
                (
                    [city] => Los Angeles, CA
                    [weather] => The weather is 22 degrees Celsius.
                )

        )

)
 */
