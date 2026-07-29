<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use OpenRouter\Client;

$client = new Client();

$models = $client->models->list(['limit' => 5, 'sort' => 'newest']);

foreach ($models as $model) {
    printf("%-40s context=%d\n", $model->id, $model->contextLength ?? 0);
}

echo "Total available: {$models->totalCount}" . PHP_EOL;
