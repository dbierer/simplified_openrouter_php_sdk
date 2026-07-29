<?php

declare(strict_types=1);

namespace OpenRouter\DTO;

final class Embedding
{
    /**
     * @param float[]|string $vector Array of floats, or a base64-encoded string
     *     when `encoding_format: "base64"` was requested.
     */
    public function __construct(
        public readonly array|string $vector,
        public readonly int $index,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            vector: $data['embedding'] ?? [],
            index: (int) ($data['index'] ?? 0),
        );
    }
}
