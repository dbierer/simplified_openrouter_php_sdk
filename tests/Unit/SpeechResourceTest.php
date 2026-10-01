<?php

declare(strict_types=1);

namespace OpenRouter\Tests\Unit;

use GuzzleHttp\Psr7\Response;
use OpenRouter\Client;
use OpenRouter\Exceptions\OpenRouterException;
use OpenRouter\Http\RetryConfig;
use OpenRouter\Tests\Support\MockClientFactory;
use PHPUnit\Framework\TestCase;

final class SpeechResourceTest extends TestCase
{
    private function audio(): Response
    {
        return new Response(200, ['Content-Type' => 'audio/mpeg'], str_repeat('A', 2000));
    }

    private function client(MockClientFactory $f): Client
    {
        return new Client(apiKey: 'k', retryConfig: new RetryConfig(maxAttempts: 1), httpClient: $f->guzzle);
    }

    public function testCreateDefaultsToMp3(): void
    {
        $f = new MockClientFactory([$this->audio()]);
        $r = $this->client($f)->speech->create(['model' => 'fish-audio/s2.1-pro', 'input' => 'hello']);

        self::assertSame(2000, strlen($r->audio));
        self::assertSame('audio/mpeg', $r->contentType);
        self::assertSame('/api/v1/audio/speech', $f->lastRequest()->getUri()->getPath());
        self::assertSame('mp3', json_decode((string) $f->lastRequest()->getBody(), true)['response_format']);
    }

    public function testJsonBodyIsRejected(): void
    {
        $f = new MockClientFactory([new Response(200, ['Content-Type' => 'application/json'], '{"x":1}')]);
        $this->expectException(OpenRouterException::class);
        $this->client($f)->speech->create(['model' => 'm', 'input' => 'hi']);
    }

    public function testFallbackAfterClientError(): void
    {
        $f = new MockClientFactory([new Response(400, [], '{"error":{"message":"no"}}'), $this->audio()]);
        $r = $this->client($f)->speech->createWithFallback(['free', 'paid'], ['input' => 'hi'], retries: 3, backoffSeconds: 0);

        self::assertSame('paid', $r->model);
    }

    public function testRetriesTransientThenFallsBack(): void
    {
        $f = new MockClientFactory([new Response(429, [], '{"error":{"message":"slow"}}'), $this->audio()]);
        $r = $this->client($f)->speech->createWithFallback(['free', 'paid'], ['input' => 'hi'], retries: 2, backoffSeconds: 0);

        self::assertSame('free', $r->model);   // second attempt on the same model succeeded
    }
}
