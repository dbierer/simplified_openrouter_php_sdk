<?php

declare(strict_types=1);

namespace OpenRouter\DTO;

final class ChatCompletionChunkChoice
{
    /**
     * @param array<string, mixed> $delta
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public readonly int $index,
        public readonly array $delta,
        public readonly ?string $finishReason,
        public readonly array $raw = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            index: (int) ($data['index'] ?? 0),
            delta: $data['delta'] ?? [],
            finishReason: $data['finish_reason'] ?? null,
            raw: $data,
        );
    }

    /**
     * Convenience accessor for the incremental text content in this chunk, if any.
     */
    public function getContentDelta(): ?string
    {
        $content = $this->delta['content'] ?? null;

        return is_string($content) ? $content : null;
    }
}
