<?php

declare(strict_types=1);

namespace OpenRouter\Tests\Unit;

use GuzzleHttp\Psr7\Response;
use OpenRouter\Client;
use OpenRouter\Tests\Support\MockClientFactory;
use PHPUnit\Framework\TestCase;

final class GenerationsCreditsTest extends TestCase
{
    public function testGetGeneration(): void
    {
        $body = json_encode([
            'data' => [
                'id' => 'gen-abc',
                'model' => 'openai/gpt-4o',
                'provider_name' => 'OpenAI',
                'total_cost' => 0.0012,
                'tokens_prompt' => 10,
                'tokens_completion' => 5,
                'created_at' => '2026-07-29T00:00:00Z',
            ],
        ], JSON_THROW_ON_ERROR);

        $factory = new MockClientFactory([new Response(200, ['Content-Type' => 'application/json'], $body)]);
        $client = new Client(apiKey: 'test-key', httpClient: $factory->guzzle);

        $generation = $client->generations->get('gen-abc');

        self::assertSame('gen-abc', $generation->id);
        self::assertSame(0.0012, $generation->totalCost);

        $request = $factory->lastRequest();
        self::assertSame('/api/v1/generation', $request->getUri()->getPath());
        self::assertSame('id=gen-abc', $request->getUri()->getQuery());
    }

    public function testGetCredits(): void
    {
        $body = json_encode(['data' => ['total_credits' => 100.0, 'total_usage' => 25.5]], JSON_THROW_ON_ERROR);
        $factory = new MockClientFactory([new Response(200, ['Content-Type' => 'application/json'], $body)]);
        $client = new Client(apiKey: 'test-key', httpClient: $factory->guzzle);

        $credits = $client->credits->get();

        self::assertSame(100.0, $credits->totalCredits);
        self::assertSame(74.5, $credits->getRemaining());
    }
}
