<?php

declare(strict_types=1);

namespace OpenRouter\DTO;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, Model>
 */
final class ModelList implements IteratorAggregate, Countable
{
    /**
     * @param Model[] $data
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public readonly array $data,
        public readonly int $totalCount,
        public readonly ?string $nextPageUrl,
        public readonly array $raw = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $models = array_map(
            static fn (array $model): Model => Model::fromArray($model),
            $data['data'] ?? [],
        );

        return new self(
            data: $models,
            totalCount: (int) ($data['total_count'] ?? count($models)),
            nextPageUrl: $data['links']['next'] ?? null,
            raw: $data,
        );
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    public function count(): int
    {
        return count($this->data);
    }
}
