<?php

declare(strict_types=1);

namespace OpenRouter\Exceptions;

/**
 * Raised whenever the OpenRouter API responds with an HTTP error status.
 *
 * Mirrors the error envelope used by the API:
 * {"error": {"code": int, "message": string, "metadata": object|null}, ...}
 */
class ApiException extends OpenRouterException
{
    /**
     * @param array<string, mixed> $headers
     * @param array<string, mixed>|null $metadata
     */
    public function __construct(
        string $message,
        private readonly int $statusCode,
        private readonly string $body,
        private readonly array $headers = [],
        private readonly ?array $metadata = null,
    ) {
        parent::__construct($message, $statusCode);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /** @return array<string, mixed> */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /** @return array<string, mixed>|null */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    /**
     * Build the appropriate exception subclass for a given HTTP status code.
     *
     * @param array<string, mixed> $headers
     */
    public static function forStatusCode(
        int $statusCode,
        string $body,
        array $headers = [],
    ): self {
        $decoded = json_decode($body, true);
        $error = is_array($decoded) ? ($decoded['error'] ?? null) : null;

        $message = is_array($error) && isset($error['message'])
            ? (string) $error['message']
            : ($body !== '' ? $body : "OpenRouter API error (HTTP {$statusCode})");

        $metadata = is_array($error) && isset($error['metadata']) && is_array($error['metadata'])
            ? $error['metadata']
            : null;

        $class = match ($statusCode) {
            400 => BadRequestException::class,
            401 => UnauthorizedException::class,
            402 => PaymentRequiredException::class,
            403 => ForbiddenException::class,
            404 => NotFoundException::class,
            408 => RequestTimeoutException::class,
            413 => PayloadTooLargeException::class,
            422 => UnprocessableEntityException::class,
            429 => TooManyRequestsException::class,
            500 => InternalServerException::class,
            502 => BadGatewayException::class,
            503 => ServiceUnavailableException::class,
            524 => EdgeNetworkTimeoutException::class,
            529 => ProviderOverloadedException::class,
            default => self::class,
        };

        return new $class($message, $statusCode, $body, $headers, $metadata);
    }
}
