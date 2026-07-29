<?php

declare(strict_types=1);

namespace OpenRouter\Tests\Unit;

use GuzzleHttp\Psr7\Response;
use OpenRouter\Client;
use OpenRouter\Tests\Support\MockClientFactory;
use PHPUnit\Framework\TestCase;

final class ModelsResourceTest extends TestCase
{
    public function testListParsesModelsAndPagination(): void
    {
        $body = json_encode([
            'data' => [
                [
                    'id' => 'openai/gpt-4o',
                    'name' => 'GPT-4o',
                    'description' => 'A model',
                    'context_length' => 128000,
                    'created' => 1700000000,
                    'supported_parameters' => ['temperature', 'tools'],
                    'pricing' => ['prompt' => '0.000005', 'completion' => '0.000015'],
                    'architecture' => ['modality' => 'text->text'],
                    'top_provider' => ['is_moderated' => false],
                ],
            ],
            'links' => ['next' => 'https://openrouter.ai/api/v1/models?offset=1'],
            'total_count' => 1,
        ], JSON_THROW_ON_ERROR);

        $factory = new MockClientFactory([new Response(200, ['Content-Type' => 'application/json'], $body)]);
        $client = new Client(apiKey: 'test-key', httpClient: $factory->guzzle);

        $list = $client->models->list(['limit' => 10]);

        self::assertSame(1, $list->totalCount);
        self::assertCount(1, $list);
        self::assertSame('openai/gpt-4o', $list->data[0]->id);
        self::assertSame(128000, $list->data[0]->contextLength);
        self::assertSame('0.000005', $list->data[0]->getPricing()['prompt']);
        self::assertStringContainsString('offset=1', (string) $list->nextPageUrl);

        $request = $factory->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('/api/v1/models', $request->getUri()->getPath());
        self::assertSame('limit=10', $request->getUri()->getQuery());
    }

    public function testGetSingleModel(): void
    {
        $body = json_encode([
            'data' => [
                'id' => 'openai/gpt-4o',
                'name' => 'GPT-4o',
                'created' => 1700000000,
                'supported_parameters' => [],
            ],
        ], JSON_THROW_ON_ERROR);

        $factory = new MockClientFactory([new Response(200, ['Content-Type' => 'application/json'], $body)]);
        $client = new Client(apiKey: 'test-key', httpClient: $factory->guzzle);

        $model = $client->models->get('openai', 'gpt-4o');

        self::assertSame('openai/gpt-4o', $model->id);
        self::assertSame('/api/v1/model/openai/gpt-4o', $factory->lastRequest()->getUri()->getPath());
    }

    public function testCount(): void
    {
        $body = json_encode(['data' => ['count' => 42]], JSON_THROW_ON_ERROR);
        $factory = new MockClientFactory([new Response(200, ['Content-Type' => 'application/json'], $body)]);
        $client = new Client(apiKey: 'test-key', httpClient: $factory->guzzle);

        self::assertSame(42, $client->models->count());
    }
}
