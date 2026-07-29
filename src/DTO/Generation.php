<?php

declare(strict_types=1);

namespace OpenRouter\DTO;

/**
 * Request & usage metadata for a single generation, as returned by
 * GET /generation?id=...
 */
final class Generation
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public readonly string $id,
        public readonly string $model,
        public readonly ?string $providerName,
        public readonly float $totalCost,
        public readonly ?int $tokensPrompt,
        public readonly ?int $tokensCompletion,
        public readonly ?int $nativeTokensPrompt,
        public readonly ?int $nativeTokensCompletion,
        public readonly ?string $finishReason,
        public readonly ?bool $streamed,
        public readonly ?bool $cancelled,
        public readonly string $createdAt,
        public readonly array $raw = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            model: (string) ($data['model'] ?? ''),
            providerName: $data['provider_name'] ?? null,
            totalCost: (float) ($data['total_cost'] ?? 0.0),
            tokensPrompt: isset($data['tokens_prompt']) ? (int) $data['tokens_prompt'] : null,
            tokensCompletion: isset($data['tokens_completion']) ? (int) $data['tokens_completion'] : null,
            nativeTokensPrompt: isset($data['native_tokens_prompt']) ? (int) $data['native_tokens_prompt'] : null,
            nativeTokensCompletion: isset($data['native_tokens_completion']) ? (int) $data['native_tokens_completion'] : null,
            finishReason: $data['finish_reason'] ?? null,
            streamed: $data['streamed'] ?? null,
            cancelled: $data['cancelled'] ?? null,
            createdAt: (string) ($data['created_at'] ?? ''),
            raw: $data,
        );
    }
}
