<?php

declare(strict_types=1);

namespace OpenRouter\DTO;

final class ChatChoice
{
    /**
     * @param array<string, mixed>|null $logprobs
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public readonly int $index,
        public readonly ChatMessage $message,
        public readonly ?string $finishReason,
        public readonly ?array $logprobs = null,
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
            message: ChatMessage::fromArray($data['message'] ?? []),
            finishReason: $data['finish_reason'] ?? null,
            logprobs: $data['logprobs'] ?? null,
            raw: $data,
        );
    }
}
