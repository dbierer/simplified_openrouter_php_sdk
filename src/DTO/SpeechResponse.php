<?php

declare(strict_types=1);

namespace OpenRouter\DTO;

final class SpeechResponse
{
    public function __construct(
        public readonly string $audio,
        public readonly string $contentType,
        public readonly string $model,
    ) {
    }

    public function save(string $path): void
    {
        if (file_put_contents($path, $this->audio) === false) {
            throw new \RuntimeException("Unable to write $path");
        }
    }
}
