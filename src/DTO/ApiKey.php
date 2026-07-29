<?php

declare(strict_types=1);

namespace OpenRouter\DTO;

final class ApiKey
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public readonly string $hash,
        public readonly string $name,
        public readonly string $label,
        public readonly bool $disabled,
        public readonly ?float $limit,
        public readonly ?float $limitRemaining,
        public readonly ?string $limitReset,
        public readonly float $usage,
        public readonly string $createdAt,
        public readonly ?string $updatedAt,
        public readonly string $workspaceId,
        public readonly ?string $creatorUserId,
        public readonly ?string $expiresAt,
        /** Plaintext API key. Only ever present on the response from {@see \OpenRouter\Resources\ApiKeysResource::create()}. */
        public readonly ?string $key = null,
        public readonly array $raw = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            hash: (string) ($data['hash'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            label: (string) ($data['label'] ?? ''),
            disabled: (bool) ($data['disabled'] ?? false),
            limit: isset($data['limit']) ? (float) $data['limit'] : null,
            limitRemaining: isset($data['limit_remaining']) ? (float) $data['limit_remaining'] : null,
            limitReset: $data['limit_reset'] ?? null,
            usage: (float) ($data['usage'] ?? 0.0),
            createdAt: (string) ($data['created_at'] ?? ''),
            updatedAt: $data['updated_at'] ?? null,
            workspaceId: (string) ($data['workspace_id'] ?? ''),
            creatorUserId: $data['creator_user_id'] ?? null,
            expiresAt: $data['expires_at'] ?? null,
            key: $data['key'] ?? null,
            raw: $data,
        );
    }
}
