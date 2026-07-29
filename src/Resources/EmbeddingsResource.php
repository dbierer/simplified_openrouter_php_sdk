<?php

declare(strict_types=1);

namespace OpenRouter\Resources;

use OpenRouter\DTO\EmbeddingResponse;

/**
 * POST /embeddings
 *
 * @see https://openrouter.ai/docs/api-reference/create-embeddings
 */
final class EmbeddingsResource extends AbstractResource
{
    /**
     * @param array<string, mixed> $params Required: input, model. Optional:
     *     dimensions, encoding_format, input_type, provider, user.
     */
    public function create(array $params): EmbeddingResponse
    {
        $response = $this->transport->request('POST', '/embeddings', json: $params);
        $decoded = json_decode((string) $response->getBody(), true);

        return EmbeddingResponse::fromArray(is_array($decoded) ? $decoded : []);
    }
}
