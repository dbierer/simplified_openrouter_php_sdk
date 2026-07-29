<?php

declare(strict_types=1);

namespace OpenRouter\Resources;

use OpenRouter\DTO\Generation;

/**
 * GET /generation
 *
 * @see https://openrouter.ai/docs/api-reference/get-a-generation
 */
final class GenerationsResource extends AbstractResource
{
    /**
     * Get request & usage metadata for a generation by its ID.
     */
    public function get(string $id): Generation
    {
        $response = $this->transport->request('GET', '/generation', query: ['id' => $id]);
        $decoded = json_decode((string) $response->getBody(), true);

        return Generation::fromArray(is_array($decoded['data'] ?? null) ? $decoded['data'] : []);
    }
}
