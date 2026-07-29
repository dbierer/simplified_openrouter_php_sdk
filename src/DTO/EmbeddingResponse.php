<?php

declare(strict_types=1);

namespace OpenRouter\DTO;

final class EmbeddingResponse
{
    /**
     * @param Embedding[] $data
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public readonly array $data,
        public readonly string $model,
        public readonly ?Usage $usage,
        public readonly array $raw = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $embeddings = array_map(
            static fn (array $item): Embedding => Embedding::fromArray($item),
            $data['data'] ?? [],
        );

        return new self(
            data: $embeddings,
            model: (string) ($data['model'] ?? ''),
            usage: isset($data['usage']) ? Usage::fromArray($data['usage']) : null,
            raw: $data,
        );
    }
}
