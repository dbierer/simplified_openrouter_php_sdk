<?php

declare(strict_types=1);

namespace OpenRouter\Resources;

use OpenRouter\DTO\SpeechResponse;
use OpenRouter\Exceptions\ApiException;
use OpenRouter\Exceptions\OpenRouterException;

/**
 * POST /audio/speech : text-to-speech, including stateless voice cloning
 * through `input_references` (see OpenRouter\DTO\InputReference::voice()).
 * The response body is the raw audio.
 */
final class SpeechResource extends AbstractResource
{
    /**
     * @param array<string, mixed> $params Required: model, input. Optional: voice, response_format
     *     (default mp3), speed, input_references.
     */
    public function create(array $params): SpeechResponse
    {
        $params += ['response_format' => 'mp3'];
        $response = $this->transport->request('POST', '/audio/speech', json: $params, headers: ['Accept' => '*/*']);
        $audio = (string) $response->getBody();
        $type = $response->getHeaderLine('Content-Type');
        if (str_contains($type, 'json') || strlen($audio) < 1000) {
            throw new OpenRouterException('Unexpected speech response (' . $type . '): ' . substr($audio, 0, 300));
        }

        return new SpeechResponse($audio, $type, (string) ($params['model'] ?? ''));
    }

    /**
     * Try each model in order, retrying transient failures (408/429/5xx and bad payloads) per model.
     * Other client errors skip straight to the next model.
     *
     * @param string[] $models primary first, then fallbacks
     * @param array<string, mixed> $params as create(), without "model"
     * @param int $retries attempts per model
     * @param int $backoffSeconds sleep multiplier between attempts (attempt * backoff)
     */
    public function createWithFallback(array $models, array $params, int $retries = 3, int $backoffSeconds = 4): SpeechResponse
    {
        $errors = [];
        foreach (array_values(array_unique(array_filter($models))) as $model) {
            for ($attempt = 1; $attempt <= $retries; $attempt++) {
                try {
                    return $this->create(['model' => $model] + $params);
                } catch (ApiException $e) {
                    $errors[] = "[$model #$attempt] " . $e->getMessage();
                    $code = $e->getStatusCode();
                    if ($code !== 408 && $code !== 429 && $code < 500) {
                        break;
                    }
                } catch (\Throwable $e) {
                    $errors[] = "[$model #$attempt] " . $e->getMessage();
                }
                if ($attempt < $retries && $backoffSeconds > 0) {
                    $this->transport->pause($backoffSeconds * $attempt * 1000);
                }
            }
        }

        throw new OpenRouterException("TTS failed on all models:\n" . implode("\n", $errors));
    }
}
