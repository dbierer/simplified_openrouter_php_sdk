<?php
declare(strict_types=1);
namespace OpenRouter\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use OpenRouter\Exceptions\ApiException;
use OpenRouter\Exceptions\TransportException;
use Psr\Http\Message\ResponseInterface;

/**
 * Thin wrapper around a Guzzle client that applies OpenRouter's default
 * headers, retry/backoff policy, and maps error responses to exceptions.
 */
final class Transport
{
    private ClientInterface $client;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl = 'https://openrouter.ai/api/v1',
        private readonly ?string $httpReferer = null,
        private readonly ?string $xTitle = null,
        private readonly ?string $xCategories = null,
        private readonly float $timeoutSeconds = 60.0,
        private readonly RetryConfig $retryConfig = new RetryConfig(),
        ?ClientInterface $client = null,
    ) {
        $this->client = $client ?? new GuzzleClient();
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed>|null $json
     * @param array<string, string> $headers
     */
    public function request(
        string $method,
        string $path,
        array $query = [],
        ?array $json = null,
        array $headers = [],
        bool $stream = false,
    ): ResponseInterface {
        $options = [
            'headers' => $this->buildHeaders($headers, $stream),
            'timeout' => $this->timeoutSeconds,
            'http_errors' => false,
            'stream' => $stream,
        ];

        if ($query !== []) {
            $options['query'] = $query;
        }

        if ($json !== null) {
            $options['json'] = $json;
        }

        $url = rtrim($this->baseUrl, '/') . $path;

        $attempt = 0;
        $startedAt = microtime(true);

        while (true) {
            try {
                $response = $this->client->request($method, $url, $options);
            } catch (GuzzleException $e) {
                if ($this->shouldRetry($attempt, $startedAt)) {
                    $this->sleep($attempt);
                    $attempt++;

                    continue;
                }

                throw new TransportException(
                    "Request to {$url} failed: " . $e->getMessage(),
                    previous: $e,
                );
            }

            $status = $response->getStatusCode();

            if ($status >= 500 && $this->shouldRetry($attempt, $startedAt)) {
                $this->sleep($attempt);
                $attempt++;

                continue;
            }

            if ($status >= 400) {
                $body = (string) $response->getBody();

                throw ApiException::forStatusCode($status, $body, $response->getHeaders());
            }

            return $response;
        }
    }

    private function shouldRetry(int $attempt, float $startedAt): bool
    {
        if ($attempt + 1 >= $this->retryConfig->maxAttempts) {
            return false;
        }

        $elapsedMs = (microtime(true) - $startedAt) * 1000;

        if ($elapsedMs >= $this->retryConfig->maxElapsedTimeMs) {
            return false;
        }

        return true;
    }

    private function sleep(int $attempt): void
    {
        $this->pause($this->retryConfig->delayForAttempt($attempt));
    }

    /**
     * Wait between attempts: through RetryConfig's sleeper when one is set,
     * otherwise a blocking usleep().
     */
    public function pause(int $delayMs): void
    {
        if ($this->retryConfig->sleeper !== null) {
            ($this->retryConfig->sleeper)($delayMs);

            return;
        }

        usleep($delayMs * 1000);
    }

    /**
     * @param array<string, string> $extra
     * @return array<string, string>
     */
    private function buildHeaders(array $extra, bool $stream): array
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => $stream ? 'text/event-stream' : 'application/json',
            'User-Agent' => 'openrouter-php-sdk/1.0',
        ];

        if ($this->httpReferer !== null) {
            $headers['HTTP-Referer'] = $this->httpReferer;
        }

        if ($this->xTitle !== null) {
            $headers['X-Title'] = $this->xTitle;
        }

        if ($this->xCategories !== null) {
            $headers['X-OpenRouter-Categories'] = $this->xCategories;
        }

        return array_merge($headers, $extra);
    }
}
