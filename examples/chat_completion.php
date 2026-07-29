<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use OpenRouter\Client;
use OpenRouter\DTO\ChatMessage;

// Reads OPENROUTER_API_KEY from the environment by default.
$client = new Client();

$response = $client->chat->create([
    'model' => 'openai/gpt-4o-mini',
    'messages' => [
        ChatMessage::system('You are a helpful assistant.'),
        ChatMessage::user('Write a haiku about PHP.'),
    ],
    'temperature' => 0.7,
]);

echo $response->getContent() . PHP_EOL;
echo "Tokens used: {$response->usage->totalTokens}" . PHP_EOL;
