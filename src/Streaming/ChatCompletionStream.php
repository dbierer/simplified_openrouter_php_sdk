<?php

declare(strict_types=1);

namespace OpenRouter\Streaming;

use IteratorAggregate;
use OpenRouter\DTO\ChatCompletionChunk;
use Psr\Http\Message\ResponseInterface;
use Traversable;

/**
 * Lazily iterates a streamed chat completion response, yielding one
 * {@see ChatCompletionChunk} per server-sent event.
 *
 * @implements IteratorAggregate<int, ChatCompletionChunk>
 */
final class ChatCompletionStream implements IteratorAggregate
{
    public function __construct(private readonly ResponseInterface $response)
    {
    }

    /**
     * @return Traversable<int, ChatCompletionChunk>
     */
    public function getIterator(): Traversable
    {
        foreach (SseParser::parse($this->response->getBody()) as $payload) {
            yield ChatCompletionChunk::fromArray($payload);
        }
    }
}
