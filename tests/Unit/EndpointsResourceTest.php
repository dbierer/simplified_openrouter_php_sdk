<?php

declare(strict_types=1);

namespace OpenRouter\Tests\Unit;

use GuzzleHttp\Psr7\Response;
use OpenRouter\Client;
use OpenRouter\Tests\Support\MockClientFactory;
use PHPUnit\Framework\TestCase;

final class EndpointsResourceTest extends TestCase
{
    public function testForModelUnwrapsDataEnvelope(): void
    {
        // The live API wraps the body in {"data": {...}}, unlike the shape
        // documented in the upstream Python SDK's operation typed dicts.
        $body = json_encode([
            'data' => [
                'id' => 'openai/gpt-4o',
                'name' => 'OpenAI: GPT-4o',
                'endpoints' => [
                    ['provider_name' => 'Azure', 'context_length' => 128000],
                    ['provider_name' => 'OpenAI', 'context_length' => 128000],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $factory = new MockClientFactory([new Response(200, [], $body)]);
        $client = new Client(apiKey: 'test-key', httpClient: $factory->guzzle);

        $result = $client->endpoints->forModel('openai', 'gpt-4o');

        self::assertSame('openai/gpt-4o', $result['id']);
        self::assertCount(2, $result['endpoints']);
        self::assertSame('/api/v1/models/openai/gpt-4o/endpoints', $factory->lastRequest()->getUri()->getPath());
    }
}
