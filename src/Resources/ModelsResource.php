<?php

declare(strict_types=1);

namespace OpenRouter\Resources;

use OpenRouter\DTO\Model;
use OpenRouter\DTO\ModelList;

/**
 * GET /models, GET /models/count, GET /model/{author}/{slug}
 *
 * @see https://openrouter.ai/docs/api-reference/list-available-models
 */
final class ModelsResource extends AbstractResource
{
    /**
     * List all models and their properties.
     *
     * @param array<string, mixed> $query Optional filters: offset, limit,
     *     category, supported_parameters, sort, q, min_price, max_price, ...
     *     (see the OpenRouter API docs for the full set).
     */
    public function list(array $query = []): ModelList
    {
        $response = $this->transport->request('GET', '/models', query: $query);
        $decoded = json_decode((string) $response->getBody(), true);

        return ModelList::fromArray(is_array($decoded) ? $decoded : []);
    }

    /**
     * Get a single model by its author and slug (e.g. "openai", "gpt-4").
     */
    public function get(string $author, string $slug): Model
    {
        $response = $this->transport->request('GET', "/model/{$author}/{$slug}");
        $decoded = json_decode((string) $response->getBody(), true);

        return Model::fromArray(is_array($decoded['data'] ?? null) ? $decoded['data'] : []);
    }

    /**
     * Get the total count of available models.
     *
     * @param array<string, mixed> $query Optional filters, e.g. output_modalities.
     */
    public function count(array $query = []): int
    {
        $response = $this->transport->request('GET', '/models/count', query: $query);
        $decoded = json_decode((string) $response->getBody(), true);

        return (int) ($decoded['data']['count'] ?? 0);
    }
}
