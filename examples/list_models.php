<?php
// usage: php examples/chat_completion.php [LIMIT|all]
require __DIR__ . '/../vendor/autoload.php';
use OpenRouter\Client;
use OpenRouter\Resources\ModelsResource;
// if $limit === 0 models() returns all
$limit = $argv[1] ?? ModelsResource::DEFAULT_COUNT;
$client = new Client();
if (strtoupper($limit) === 'ALL') {
    $limit = $client->models->count();
}
$models = $client->models->list(['limit' => $limit, 'sort' => 'newest']);
foreach ($models as $model) {
    printf("%-40s context=%d\n", $model->id, $model->contextLength ?? 0);
}
echo "Total available: {$models->totalCount}" . PHP_EOL;
