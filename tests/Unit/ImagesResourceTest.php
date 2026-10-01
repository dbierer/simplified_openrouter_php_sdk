<?php

declare(strict_types=1);

namespace OpenRouter\Tests\Unit;

use GuzzleHttp\Psr7\Response;
use OpenRouter\Client;
use OpenRouter\DTO\InputReference;
use OpenRouter\Exceptions\OpenRouterException;
use OpenRouter\Http\RetryConfig;
use OpenRouter\Tests\Support\MockClientFactory;
use PHPUnit\Framework\TestCase;

final class ImagesResourceTest extends TestCase
{
    private function ok(): Response
    {
        return new Response(200, [], json_encode(['data' => [['b64_json' => base64_encode('PNGDATA'), 'media_type' => 'image/png']]]));
    }

    private function client(MockClientFactory $f): Client
    {
        return new Client(apiKey: 'k', retryConfig: new RetryConfig(maxAttempts: 1), httpClient: $f->guzzle);
    }

    public function testGenerate(): void
    {
        $f = new MockClientFactory([$this->ok()]);
        $r = $this->client($f)->images->generate([
            'model' => 'qwen/qwen-image-3-pro', 'prompt' => 'a cat', 'size' => '512x512',
            'input_references' => [InputReference::imageFromBase64('QUJD')],
        ]);

        self::assertSame('PNGDATA', $r->first()->bytes());
        self::assertSame('image/png', $r->first()->mediaType);
        self::assertSame('/api/v1/images', $f->lastRequest()->getUri()->getPath());
        $body = json_decode((string) $f->lastRequest()->getBody(), true);
        self::assertSame('data:image/png;base64,QUJD', $body['input_references'][0]['image_url']['url']);
    }

    public function testFallbackUsesSecondModel(): void
    {
        $f = new MockClientFactory([new Response(400, [], '{"error":{"message":"bad"}}'), $this->ok()]);
        $r = $this->client($f)->images->generateWithFallback(['m1', 'm2'], ['prompt' => 'x']);

        self::assertSame('m2', $r->model);
    }

    public function testAllModelsFail(): void
    {
        $f = new MockClientFactory([new Response(400, [], '{"error":{"message":"a"}}'), new Response(400, [], '{"error":{"message":"b"}}')]);
        $this->expectException(OpenRouterException::class);
        $this->client($f)->images->generateWithFallback(['m1', 'm2'], ['prompt' => 'x']);
    }
}
