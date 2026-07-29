<?php

declare(strict_types=1);

namespace OpenRouter\DTO;

final class Usage
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public readonly int $promptTokens,
        public readonly int $completionTokens,
        public readonly int $totalTokens,
        public readonly ?float $cost,
        public readonly array $raw = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            promptTokens: (int) ($data['prompt_tokens'] ?? 0),
            completionTokens: (int) ($data['completion_tokens'] ?? 0),
            totalTokens: (int) ($data['total_tokens'] ?? 0),
            cost: isset($data['cost']) ? (float) $data['cost'] : null,
            raw: $data,
        );
    }
}
