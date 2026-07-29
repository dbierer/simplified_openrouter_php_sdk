<?php

declare(strict_types=1);

namespace OpenRouter\Tests\Unit;

use GuzzleHttp\Psr7\Response;
use OpenRouter\Client;
use OpenRouter\DTO\ChatMessage;
use OpenRouter\Tests\Support\MockClientFactory;
use PHPUnit\Framework\TestCase;

final class ChatResourceTest extends TestCase
{
    public function testCreateReturnsParsedResponse(): void
    {
        $body = json_encode([
            'id' => 'gen-123',
            'object' => 'chat.completion',
            'created' => 1700000000,
            'model' => 'openai/gpt-4o',
            'choices' => [
                [
                    'index' => 0,
                    'message' => ['role' => 'assistant', 'content' => 'Hello there!'],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 3, 'total_tokens' => 13],
        ], JSON_THROW_ON_ERROR);

        $factory = new MockClientFactory([new Response(200, ['Content-Type' => 'application/json'], $body)]);
        $client = new Client(apiKey: 'test-key', httpClient: $factory->guzzle);

        $response = $client->chat->create([
            'model' => 'openai/gpt-4o',
            'messages' => [ChatMessage::user('Hi')],
        ]);

        self::assertSame('gen-123', $response->id);
        self::assertSame('Hello there!', $response->getContent());
        self::assertSame(13, $response->usage->totalTokens);

        $request = $factory->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/api/v1/chat/completions', $request->getUri()->getPath());
        self::assertSame('Bearer test-key', $request->getHeaderLine('Authorization'));

        $sentBody = json_decode((string) $request->getBody(), true);
        self::assertFalse($sentBody['stream']);
        self::assertSame([['role' => 'user', 'content' => 'Hi']], $sentBody['messages']);
    }

    public function testCreateStreamedYieldsChunks(): void
    {
        $sse = "data: " . json_encode(['id' => 'c1', 'object' => 'chat.completion.chunk', 'created' => 1, 'model' => 'm', 'choices' => [['index' => 0, 'delta' => ['content' => 'Hel'], 'finish_reason' => null]]]) . "\n\n"
            . "data: " . json_encode(['id' => 'c1', 'object' => 'chat.completion.chunk', 'created' => 1, 'model' => 'm', 'choices' => [['index' => 0, 'delta' => ['content' => 'lo'], 'finish_reason' => 'stop']]]) . "\n\n"
            . "data: [DONE]\n\n";

        $factory = new MockClientFactory([new Response(200, ['Content-Type' => 'text/event-stream'], $sse)]);
        $client = new Client(apiKey: 'test-key', httpClient: $factory->guzzle);

        $stream = $client->chat->createStreamed([
            'model' => 'm',
            'messages' => [ChatMessage::user('Hi')],
        ]);

        $collected = '';
        $chunkCount = 0;

        foreach ($stream as $chunk) {
            $chunkCount++;
            $collected .= $chunk->getContentDelta();
        }

        self::assertSame(2, $chunkCount);
        self::assertSame('Hello', $collected);

        $request = $factory->lastRequest();
        $sentBody = json_decode((string) $request->getBody(), true);
        self::assertTrue($sentBody['stream']);
        self::assertSame('text/event-stream', $request->getHeaderLine('Accept'));
    }
}
