<?php

declare(strict_types=1);

namespace OpenRouter\Tests\Unit;

use GuzzleHttp\Psr7\Response;
use OpenRouter\Client;
use OpenRouter\Tests\Support\MockClientFactory;
use PHPUnit\Framework\TestCase;

final class EmbeddingsResourceTest extends TestCase
{
    public function testCreate(): void
    {
        $body = json_encode([
            'object' => 'list',
            'model' => 'openai/text-embedding-3-small',
            'data' => [
                ['object' => 'embedding', 'embedding' => [0.1, 0.2, 0.3], 'index' => 0],
            ],
            'usage' => ['prompt_tokens' => 4, 'completion_tokens' => 0, 'total_tokens' => 4],
        ], JSON_THROW_ON_ERROR);

        $factory = new MockClientFactory([new Response(200, [], $body)]);
        $client = new Client(apiKey: 'test-key', httpClient: $factory->guzzle);

        $result = $client->embeddings->create([
            'model' => 'openai/text-embedding-3-small',
            'input' => 'hello world',
        ]);

        self::assertCount(1, $result->data);
        self::assertSame([0.1, 0.2, 0.3], $result->data[0]->vector);
        self::assertSame(4, $result->usage->totalTokens);
        self::assertSame('/api/v1/embeddings', $factory->lastRequest()->getUri()->getPath());
    }
}
