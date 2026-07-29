<?php

declare(strict_types=1);

namespace OpenRouter\Resources;

/**
 * GET /models/{author}/{slug}/endpoints
 *
 * Returns the unwrapped `data` object: {id, name, description, created,
 * architecture, endpoints: [...]}. Each endpoint's shape varies by provider
 * (pricing, context_length, quantization, ...), so it is left as a raw array
 * rather than modeled as a DTO.
 *
 * @see https://openrouter.ai/docs/api-reference/list-endpoints-for-a-model
 */
final class EndpointsResource extends AbstractResource
{
    /**
     * List all provider endpoints available for a given model.
     *
     * @return array<string, mixed>
     */
    public function forModel(string $author, string $slug): array
    {
        $response = $this->transport->request('GET', "/models/{$author}/{$slug}/endpoints");
        $decoded = json_decode((string) $response->getBody(), true);

        return is_array($decoded['data'] ?? null) ? $decoded['data'] : [];
    }
}
