<?php
require __DIR__ . '/../vendor/autoload.php';

use OpenRouter\Client;
use OpenRouter\DTO\ChatMessage;

$client = new Client();

$stream = $client->chat->createStreamed([
    'model' => 'openai/gpt-4o-mini',
    'messages' => [ChatMessage::user('Count from 1 to 5, one number per line.')],
]);

foreach ($stream as $chunk) {
    echo $chunk->getContentDelta();
}

echo PHP_EOL;
