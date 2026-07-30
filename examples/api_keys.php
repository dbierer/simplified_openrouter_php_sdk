<?php
require __DIR__ . '/../vendor/autoload.php';

use OpenRouter\Client;

// These endpoints require a *management* API key, distinct from a regular
// inference key. See https://openrouter.ai/docs/guides/overview/auth/management-api-keys
$client = new Client();

$credits = $client->credits->get();
echo "Remaining credits: {$credits->getRemaining()}" . PHP_EOL;

$newKey = $client->apiKeys->create(['name' => 'example-generated-key', 'limit' => 5.0]);
echo "Created key {$newKey->hash} - plaintext (shown only now): {$newKey->key}" . PHP_EOL;

foreach ($client->apiKeys->list() as $key) {
    printf("%-10s %-30s usage=%.4f\n", $key->hash, $key->name, $key->usage);
}

$client->apiKeys->delete($newKey->hash);
echo "Deleted key {$newKey->hash}" . PHP_EOL;
