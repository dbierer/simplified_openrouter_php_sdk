<?php

declare(strict_types=1);

namespace OpenRouter\Tests\Unit;

use GuzzleHttp\Psr7\Response;
use OpenRouter\Client;
use OpenRouter\Tests\Support\MockClientFactory;
use PHPUnit\Framework\TestCase;

final class ApiKeysResourceTest extends TestCase
{
    private function keyPayload(): array
    {
        return [
            'hash' => 'abc123',
            'name' => 'My Key',
            'label' => 'My Key',
            'disabled' => false,
            'limit' => 10.0,
            'limit_remaining' => 9.5,
            'limit_reset' => null,
            'usage' => 0.5,
            'created_at' => '2026-01-01T00:00:00Z',
            'updated_at' => null,
            'workspace_id' => 'ws-1',
            'creator_user_id' => null,
            'expires_at' => null,
        ];
    }

    public function testList(): void
    {
        $body = json_encode(['data' => [$this->keyPayload()]], JSON_THROW_ON_ERROR);
        $factory = new MockClientFactory([new Response(200, [], $body)]);
        $client = new Client(apiKey: 'test-key', httpClient: $factory->guzzle);

        $keys = $client->apiKeys->list();

        self::assertCount(1, $keys);
        self::assertSame('abc123', $keys[0]->hash);
    }

    public function testCreateExposesPlaintextKeyOnce(): void
    {
        $body = json_encode(['data' => $this->keyPayload(), 'key' => 'sk-or-plaintext'], JSON_THROW_ON_ERROR);
        $factory = new MockClientFactory([new Response(200, [], $body)]);
        $client = new Client(apiKey: 'test-key', httpClient: $factory->guzzle);

        $key = $client->apiKeys->create(['name' => 'My Key']);

        self::assertSame('sk-or-plaintext', $key->key);
        self::assertSame('POST', $factory->lastRequest()->getMethod());
        self::assertSame('/api/v1/keys', $factory->lastRequest()->getUri()->getPath());
    }

    public function testUpdateSendsPatch(): void
    {
        $body = json_encode(['data' => $this->keyPayload()], JSON_THROW_ON_ERROR);
        $factory = new MockClientFactory([new Response(200, [], $body)]);
        $client = new Client(apiKey: 'test-key', httpClient: $factory->guzzle);

        $client->apiKeys->update('abc123', ['disabled' => true]);

        $request = $factory->lastRequest();
        self::assertSame('PATCH', $request->getMethod());
        self::assertSame('/api/v1/keys/abc123', $request->getUri()->getPath());
        self::assertTrue(json_decode((string) $request->getBody(), true)['disabled']);
    }

    public function testDelete(): void
    {
        $body = json_encode(['deleted' => true], JSON_THROW_ON_ERROR);
        $factory = new MockClientFactory([new Response(200, [], $body)]);
        $client = new Client(apiKey: 'test-key', httpClient: $factory->guzzle);

        self::assertTrue($client->apiKeys->delete('abc123'));
        self::assertSame('DELETE', $factory->lastRequest()->getMethod());
    }
}
