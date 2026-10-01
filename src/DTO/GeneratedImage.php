<?php

declare(strict_types=1);

namespace OpenRouter\DTO;

final class GeneratedImage
{
    public function __construct(
        public readonly string $b64Json,
        public readonly string $mediaType = 'image/png',
        public readonly ?string $revisedPrompt = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            b64Json: (string) ($data['b64_json'] ?? ''),
            mediaType: (string) ($data['media_type'] ?? 'image/png'),
            revisedPrompt: isset($data['revised_prompt']) ? (string) $data['revised_prompt'] : null,
        );
    }

    /** Decoded image bytes. */
    public function bytes(): string
    {
        $bin = base64_decode($this->b64Json, true);
        if ($bin === false || $bin === '') {
            throw new \RuntimeException('Image payload is empty or not valid base64.');
        }

        return $bin;
    }
}
