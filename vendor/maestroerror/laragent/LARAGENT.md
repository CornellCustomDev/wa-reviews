# LarAgent - Standalone Usage Guide

LarAgent is a powerful PHP library for building AI-powered applications. While it integrates seamlessly with Laravel, it can also be used as a standalone package in any PHP project.

## Installation

1. Install via Composer:
```bash
composer require maestroerror/laragent
```

2. If you're using OpenAI, make sure to have your API key ready.

## Core Concepts

### 1. Basic Setup

```php
use LarAgent\Drivers\OpenAi\OpenAiDriver;
use LarAgent\History\InMemoryChatHistory;
use LarAgent\LarAgent;

// Initialize the driver with your API key
$driver = new OpenAiDriver(['api_key' => 'your-api-key']);

// Create a chat history instance
$chatKey = 'your-chat-key';
$chatHistory = new InMemoryChatHistory($chatKey);

// Setup LarAgent
$agent = LarAgent::setup($driver, $chatHistory, [
    'model' => 'gpt-4', // or any other model
]);


$response = $agent->withMessage("Hello World")->run();

```

### 2. Chat Histories

Chat histories maintain the conversation context. LarAgent provides:
- `InMemoryChatHistory`: For temporary storage during runtime
- `JsonChatHistory`: For storage in JSON files
- Custom implementations possible extending `LarAgent\Core\Abstractions\ChatHistory`

```php
use LarAgent\History\JsonChatHistory;

$name = 'unique-chat-key';
$chatHistory = new JsonChatHistory($name, ['folder' => __DIR__.'/json_History']);
```

### 3. Tools (Function Calling)

Tools allow LarAgent to execute functions based on AI decisions:

```php
use LarAgent\Tool;

$tool = Tool::create('get_weather', 'Get weather information')
    ->addProperty('location', 'string', 'City name') // String property
    ->addProperty('unit', 'string', 'Temperature unit', ['celsius', 'fahrenheit']) // Enum property
    ->setRequired('location')
    ->setCallback(function ($location, $unit = 'celsius') {
        // Your weather API logic here
        return "Weather info for $location in $unit";
    });

$agent->setTools([$tool]);
```

`setCallback` and method allows any PHP 'callable' type, check out [PHP docs](https://www.php.net/manual/en/language.types.callable.php) for more information.

### 4. Structured Data

Enforce structured responses using JSON schemas:

```php
use LarAgent\Message;
use LarAgent\Drivers\OpenAi\OpenAiCompatible;
use LarAgent\History\JsonChatHistory;
use LarAgent\LarAgent;

$schema = [
    'name' => 'weather_response',
    'schema' => [
        'type' => 'object',
        'properties' => [
            'temperature' => ['type' => 'number'],
            'condition' => ['type' => 'string'],
        ],
        'required' => ['temperature', 'condition']
    ],
    'strict' => true
];

$name = 'unique-chat-key';
$chatHistory = new JsonChatHistory($name, ['folder' => __DIR__.'/json_History']);

// OpenAiCompatible driver is universal for any provider which adheres to OpenAI API standards
$driver = new OpenAiCompatible(['api_key' => 'your-api-key', 'api_url' => 'https://api.openai.com/v1']);

// Setup LarAgent
$agent = LarAgent::setup($driver, $chatHistory, [
    'model' => 'gpt-4', // or any other model
]);

$agent->structured($schema);
$response = $agent->withMessage(Message::user('User message.'))->run();
// In case of using structured output, response will be an array according to the schema
print_r($response);
```

### 5. Hooks

LarAgent provides a powerful hook system that allows you to intercept and modify behavior at various points in the execution flow. Each hook can be used multiple times on the same agent instance, as they are implemented as collections of callbacks.

Available hooks:

1. **Before Reinjecting Instructions**
```php
$agent->beforeReinjectingInstructions(function ($agent, $chatHistory) {
    // Modify or validate chat history before instructions are reinjected
    // Return false to prevent reinjection
});
```

2. **Before Send**
```php
$agent->beforeSend(function ($agent, $history, $message) {
    // Intercept or modify message before it's added to chat history
    // Return false to prevent message from being sent
});
```

3. **After Send**
```php
$agent->afterSend(function ($agent, $history, $message) {
    // Process message after it's been added to chat history
    // Track token usage, log conversations, etc.
    // Return false to interrupt the chain
});
```

4. **Before Save History**
```php
$agent->beforeSaveHistory(function ($agent, $history) {
    // Modify or validate chat history before saving
    // Return false to prevent saving
});
```

5. **Before Response**
```php
$agent->beforeResponse(function ($agent, $history, $message) {
    // Intercept or modify message before sending to LLM
    // Return false to prevent sending to LLM
});
```

6. **After Response**
```php
$agent->afterResponse(function ($agent, $message) {
    // Process LLM's response before further processing
    // Return false to interrupt the chain
});
```

7. **Before Tool Execution**
```php
$agent->beforeToolExecution(function ($agent, $tool) {
    // Validate or modify tool before execution
    // Return false to prevent tool execution
});
```

8. **After Tool Execution**
```php
$agent->afterToolExecution(function ($agent, $tool, &$result) {
    // Process or modify tool execution results
    // Note: $result is passed by reference and can be modified
    // Return false to interrupt the chain
});
```

9. **Before Structured Output**
```php
$agent->beforeStructuredOutput(function ($agent, &$response) {
    // Modify the structured output before it's returned
    // Note: $response is passed by reference and can be modified
    // Return false to prevent further processing
});
```

Example of using multiple hooks:

```php
$agent
    ->beforeResponse(function ($agent, $history, $message) {
        // First hook for logging
        LogService::info('Sending message to LLM', ['message' => $message]);
    })
    ->beforeResponse(function ($agent, $history, $message) {
        // Second hook for message modification
        $message->setContent('[Modified] ' . $message->getContent());
    })
    ->afterToolExecution(function ($agent, $tool, &$result) {
        // Cache tool results
        CacheService::put("tool_{$tool->getName()}", $result, 3600);
    });
```

### 6. Basic Usage Example

```php
// Setup agent with tools and structured output
$agent->setTools([$weatherTool])
    ->structured($weatherSchema)
    ->withInstructions('You are a weather assistant', false) // false = use system role, true = use developer role
    ->withMessage(Message::user('What\'s the weather in London?'));

// Run the conversation
$response = $agent->run();
```

`$response` is an instance of `MessageInterface` and can be further processed as string `echo $response;` or as array `print_r($response); echo $response['content']` or as object `$response->getContent()`. **Note** that in case of using structured output, response content will be an array according to the schema, so you can access the response as a string.

## Configuration Options

- `model`: Set the LLM model (e.g., 'gpt-4', 'gpt-3.5-turbo')
- `temperature`: Control response randomness (0.0 to 2.0)
- `contextWindowSize`: Maximum context window size
- `maxCompletionTokens`: Maximum tokens for completion
- `parallelToolCalls`: Enable/disable parallel tool execution

```php
$agent->setModel('gpt-4')
    ->setTemperature(0.7)
    ->setContextWindowSize(50000)
    ->setMaxCompletionTokens(1000);
```

## Best Practices

1. Always handle API errors and rate limits appropriately
2. Store API keys securely (use environment variables)
3. Implement proper chat history persistence for production use
4. Set appropriate context window sizes based on your use case
5. Use structured data when you need consistent response formats

## Need Help?

- Review the test files in the repository
- Submit issues for bugs or feature requests

// @todo add discord channel invite link