<?php

declare(strict_types=1);

namespace OpenRouter\DTO;

/**
 * An AI model available on OpenRouter. Deeply-nested/rarely-used fields
 * (architecture, pricing, top_provider, benchmarks, ...) are exposed as raw
 * arrays via {@see self::$raw} rather than fully modeled, since their shape
 * varies by model type.
 */
final class Model
{
    /**
     * @param string[] $supportedParameters
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?int $contextLength,
        public readonly int $created,
        public readonly array $supportedParameters,
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
            name: (string) ($data['name'] ?? ''),
            description: $data['description'] ?? null,
            contextLength: isset($data['context_length']) ? (int) $data['context_length'] : null,
            created: (int) ($data['created'] ?? 0),
            supportedParameters: $data['supported_parameters'] ?? [],
            raw: $data,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getPricing(): array
    {
        return $this->raw['pricing'] ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getArchitecture(): array
    {
        return $this->raw['architecture'] ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getTopProvider(): array
    {
        return $this->raw['top_provider'] ?? [];
    }
}
