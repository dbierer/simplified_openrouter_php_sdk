<?php

declare(strict_types=1);

namespace OpenRouter\Resources;

use OpenRouter\DTO\ApiKey;

/**
 * /keys, /keys/{hash}, /key
 *
 * All endpoints in this resource require a management API key.
 *
 * @see https://openrouter.ai/docs/api-reference/list-api-keys
 */
final class ApiKeysResource extends AbstractResource
{
    /**
     * List all API keys for the authenticated user.
     *
     * @param array<string, mixed> $query Optional filters: include_disabled, offset, workspace_id.
     * @return ApiKey[]
     */
    public function list(array $query = []): array
    {
        $response = $this->transport->request('GET', '/keys', query: $query);
        $decoded = json_decode((string) $response->getBody(), true);

        return array_map(
            static fn (array $key): ApiKey => ApiKey::fromArray($key),
            is_array($decoded['data'] ?? null) ? $decoded['data'] : [],
        );
    }

    /**
     * Create a new API key. The plaintext key is only ever available on the
     * object returned from this call ({@see ApiKey::$key}) — it cannot be
     * retrieved later.
     *
     * @param array<string, mixed> $params Required: name. Optional:
     *     creator_user_id, expires_at, include_byok_in_limit, limit,
     *     limit_reset, workspace_id.
     */
    public function create(array $params): ApiKey
    {
        $response = $this->transport->request('POST', '/keys', json: $params);
        $decoded = json_decode((string) $response->getBody(), true);

        $data = is_array($decoded['data'] ?? null) ? $decoded['data'] : [];
        $data['key'] = $decoded['key'] ?? null;

        return ApiKey::fromArray($data);
    }

    /**
     * Get a single API key by its hash.
     */
    public function get(string $hash): ApiKey
    {
        $response = $this->transport->request('GET', "/keys/{$hash}");
        $decoded = json_decode((string) $response->getBody(), true);

        return ApiKey::fromArray(is_array($decoded['data'] ?? null) ? $decoded['data'] : []);
    }

    /**
     * Update an API key.
     *
     * @param array<string, mixed> $params Any of: name, disabled,
     *     include_byok_in_limit, limit, limit_reset.
     */
    public function update(string $hash, array $params): ApiKey
    {
        $response = $this->transport->request('PATCH', "/keys/{$hash}", json: $params);
        $decoded = json_decode((string) $response->getBody(), true);

        return ApiKey::fromArray(is_array($decoded['data'] ?? null) ? $decoded['data'] : []);
    }

    /**
     * Delete an API key. Returns true on success.
     */
    public function delete(string $hash): bool
    {
        $response = $this->transport->request('DELETE', "/keys/{$hash}");
        $decoded = json_decode((string) $response->getBody(), true);

        return (bool) ($decoded['deleted'] ?? false);
    }

    /**
     * Get metadata about the API key associated with the current request's
     * authentication (i.e. the key this client was constructed with).
     *
     * @return array<string, mixed>
     */
    public function current(): array
    {
        $response = $this->transport->request('GET', '/key');
        $decoded = json_decode((string) $response->getBody(), true);

        return is_array($decoded['data'] ?? null) ? $decoded['data'] : [];
    }
}
