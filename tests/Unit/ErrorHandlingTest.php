<?php

declare(strict_types=1);

namespace OpenRouter\Tests\Unit;

use GuzzleHttp\Psr7\Response;
use OpenRouter\Client;
use OpenRouter\Exceptions\InternalServerException;
use OpenRouter\Exceptions\TooManyRequestsException;
use OpenRouter\Http\RetryConfig;
use OpenRouter\Tests\Support\MockClientFactory;
use PHPUnit\Framework\TestCase;

final class ErrorHandlingTest extends TestCase
{
    public function testMapsStatusCodeToSpecificException(): void
    {
        $body = json_encode(['error' => ['code' => 429, 'message' => 'Rate limited, slow down.']], JSON_THROW_ON_ERROR);
        $factory = new MockClientFactory([new Response(429, [], $body)]);
        $client = new Client(apiKey: 'test-key', httpClient: $factory->guzzle);

        try {
            $client->models->list();
            self::fail('Expected TooManyRequestsException to be thrown.');
        } catch (TooManyRequestsException $e) {
            self::assertSame(429, $e->getStatusCode());
            self::assertSame('Rate limited, slow down.', $e->getMessage());
        }
    }

    public function testRetriesOn5xxThenSucceeds(): void
    {
        $successBody = json_encode(['data' => [], 'links' => ['next' => null], 'total_count' => 0], JSON_THROW_ON_ERROR);
        $factory = new MockClientFactory([
            new Response(500, [], json_encode(['error' => ['code' => 500, 'message' => 'boom']])),
            new Response(200, [], $successBody),
        ]);

        $client = new Client(
            apiKey: 'test-key',
            httpClient: $factory->guzzle,
            retryConfig: new RetryConfig(maxAttempts: 3, initialIntervalMs: 1, maxIntervalMs: 1),
        );

        $list = $client->models->list();

        self::assertSame(0, $list->totalCount);
        self::assertCount(2, $factory->history);
    }

    public function testGivesUpAfterMaxAttempts(): void
    {
        $errorBody = json_encode(['error' => ['code' => 500, 'message' => 'still broken']], JSON_THROW_ON_ERROR);
        $factory = new MockClientFactory([
            new Response(500, [], $errorBody),
            new Response(500, [], $errorBody),
        ]);

        $client = new Client(
            apiKey: 'test-key',
            httpClient: $factory->guzzle,
            retryConfig: new RetryConfig(maxAttempts: 2, initialIntervalMs: 1, maxIntervalMs: 1),
        );

        $this->expectException(InternalServerException::class);
        $client->models->list();
    }
}
