<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use OpenRouter\Client;

$client = new Client();

$result = $client->embeddings->create([
    'model' => 'openai/text-embedding-3-small',
    'input' => 'The quick brown fox jumps over the lazy dog.',
]);

foreach ($result->data as $embedding) {
    echo "Embedding #{$embedding->index}: " . count($embedding->vector) . ' dimensions' . PHP_EOL;
}
