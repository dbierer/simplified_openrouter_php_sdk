<?php

declare(strict_types=1);

namespace OpenRouter\DTO;

final class ChatCompletionChunk
{
    /**
     * @param ChatCompletionChunkChoice[] $choices
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public readonly string $id,
        public readonly string $object,
        public readonly int $created,
        public readonly ?string $model,
        public readonly array $choices,
        public readonly ?Usage $usage,
        public readonly array $raw = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $choices = array_map(
            static fn (array $choice): ChatCompletionChunkChoice => ChatCompletionChunkChoice::fromArray($choice),
            $data['choices'] ?? [],
        );

        return new self(
            id: (string) ($data['id'] ?? ''),
            object: (string) ($data['object'] ?? ''),
            created: (int) ($data['created'] ?? 0),
            model: $data['model'] ?? null,
            choices: $choices,
            usage: isset($data['usage']) ? Usage::fromArray($data['usage']) : null,
            raw: $data,
        );
    }

    public function getContentDelta(): ?string
    {
        return $this->choices[0]->getContentDelta() ?? null;
    }
}
