<?php

declare(strict_types=1);

namespace OpenRouter\DTO;

final class ImageResponse
{
    /**
     * @param GeneratedImage[] $data
     * @param string $model the model that actually produced the image (relevant with fallbacks)
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public readonly array $data,
        public readonly string $model = '',
        public readonly array $raw = [],
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data, string $model = ''): self
    {
        $images = [];
        foreach ($data['data'] ?? [] as $item) {
            if (is_array($item) && !empty($item['b64_json'])) {
                $images[] = GeneratedImage::fromArray($item);
            }
        }

        return new self($images, $model, $data);
    }

    public function first(): GeneratedImage
    {
        return $this->data[0] ?? throw new \RuntimeException('Response contained no image data.');
    }
}
