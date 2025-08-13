# AI Bridge

A simple PHP package to interact with various AI engines, providing a unified interface for sending prompts and receiving responses.

## Usage

The main entry point of the package is the `Potato\AiBridge\Agent` class. You can create an agent for a specific AI engine and use it to send prompts.

Here is a basic example of how to use the package:

```php
<?php

// 1. Require the Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// 2. Use the Agent class
use Potato\AiBridge\Agent;

// 3. Create an agent for your desired AI engine (e.g., 'gemini' or 'gpt')
$apiKey = 'YOUR_API_KEY'; // Replace with your actual API key
$agent = Agent::create('gemini', $apiKey);

// 4. Define the model and the instructions for the AI
$model = 'gemini-pro'; // Specify the model you want to use
$instructions = [
    'You are a helpful assistant.',
    'Translate the following English text to French: "Hello, world!"',
];

// 5. Send the prompt to the AI and get the response
try {
    $response = $agent->prompt($model, $instructions);
    echo $response;
} catch (Exception $e) {
    // Handle potential exceptions, e.g., network errors or invalid API keys
    echo 'An error occurred: ' . $e->getMessage();
}

```

### Instructions

The `prompt` method takes an array of strings as instructions. These instructions are concatenated into a single prompt that is sent to the AI engine. Each string in the array will be treated as a separate line in the prompt.

## Supported Engines

Currently, the following AI engines are supported:

*   **GPT** (`gpt`)
*   **Gemini** (`gemini`)

You can select the engine when creating the `Agent` instance.

## Testing

The package comes with a suite of tests written with PHPUnit. To run the tests, execute the following command from the root directory of the project:

```bash
./vendor/bin/phpunit
```

This will run all the unit tests and ensure that the package is working correctly.
