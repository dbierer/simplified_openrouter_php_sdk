<?php

declare(strict_types=1);

namespace OpenRouter\DTO;

final class Credits
{
    public function __construct(
        public readonly float $totalCredits,
        public readonly float $totalUsage,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            totalCredits: (float) ($data['total_credits'] ?? 0.0),
            totalUsage: (float) ($data['total_usage'] ?? 0.0),
        );
    }

    public function getRemaining(): float
    {
        return $this->totalCredits - $this->totalUsage;
    }
}
