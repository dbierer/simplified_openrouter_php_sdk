<?php

declare(strict_types=1);

namespace OpenRouter\Resources;

use OpenRouter\DTO\ChatCompletionResponse;
use OpenRouter\DTO\ChatMessage;
use OpenRouter\Streaming\ChatCompletionStream;

/**
 * POST /chat/completions
 *
 * @see https://openrouter.ai/docs/api-reference/chat-completion
 */
final class ChatResource extends AbstractResource
{
    /**
     * Create a (non-streaming) chat completion.
     *
     * @param array<string, mixed> $params Request body matching the OpenRouter
     *     Chat Completions API (model, messages, temperature, tools, ...).
     *     `messages` entries may be plain arrays or {@see ChatMessage} instances.
     */
    public function create(array $params): ChatCompletionResponse
    {
        $params = self::normalizeParams($params);
        $params['stream'] = false;

        $response = $this->transport->request('POST', '/chat/completions', json: $params);

        $decoded = json_decode((string) $response->getBody(), true);

        return ChatCompletionResponse::fromArray(is_array($decoded) ? $decoded : []);
    }

    /**
     * Create a streaming chat completion. Iterate the returned stream to
     * receive {@see \OpenRouter\DTO\ChatCompletionChunk} instances as they
     * arrive.
     *
     * @param array<string, mixed> $params Same shape as {@see self::create()}.
     */
    public function createStreamed(array $params): ChatCompletionStream
    {
        $params = self::normalizeParams($params);
        $params['stream'] = true;

        $response = $this->transport->request('POST', '/chat/completions', json: $params, stream: true);

        return new ChatCompletionStream($response);
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private static function normalizeParams(array $params): array
    {
        if (isset($params['messages']) && is_array($params['messages'])) {
            $params['messages'] = array_map(
                static fn (mixed $message): array => $message instanceof ChatMessage
                    ? $message->toArray()
                    : $message,
                $params['messages'],
            );
        }

        return $params;
    }
}
