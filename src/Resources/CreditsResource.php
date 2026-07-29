<?php

declare(strict_types=1);

namespace OpenRouter\Resources;

use OpenRouter\DTO\Credits;

/**
 * GET /credits
 *
 * Requires a management API key.
 *
 * @see https://openrouter.ai/docs/api-reference/get-credits
 */
final class CreditsResource extends AbstractResource
{
    public function get(): Credits
    {
        $response = $this->transport->request('GET', '/credits');
        $decoded = json_decode((string) $response->getBody(), true);

        return Credits::fromArray(is_array($decoded['data'] ?? null) ? $decoded['data'] : []);
    }
}
