<?php
// Usage: OPENROUTER_API_KEY=... php examples/images_and_speech.php
require __DIR__ . '/../vendor/autoload.php';

use OpenRouter\Client;

$client = new Client(timeoutSeconds: 300.0);

$img = $client->images->generateWithFallback(
    ['qwen/qwen-image-3-pro', 'qwen/qwen-image-3'],
    ['prompt' => 'A lighthouse at dawn, comic-strip style', 'size' => '1024x1024'],
);
file_put_contents('lighthouse.png', $img->first()->bytes());
echo "image via {$img->model}\n";

$speech = $client->speech->create(['model' => 'fish-audio/s2.1-pro', 'input' => 'The lighthouse at dawn.']);
$speech->save('lighthouse.mp3');
echo "speech via {$speech->model}\n";
