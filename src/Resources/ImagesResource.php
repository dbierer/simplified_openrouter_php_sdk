<?php

declare(strict_types=1);

namespace OpenRouter\Resources;

use OpenRouter\DTO\ImageResponse;
use OpenRouter\Exceptions\OpenRouterException;

/**
 * POST /images : dedicated image-generation endpoint.
 * Response shape: {"data":[{"b64_json":"...","media_type":"image/png"}]}
 *
 * @see https://openrouter.ai/docs/guides/overview/multimodal/image-generation
 */
final class ImagesResource extends AbstractResource
{
    /**
     * @param array<string, mixed> $params Required: model, prompt. Optional: size (e.g. "1024x1024"),
     *     input_references (see OpenRouter\DTO\InputReference).
     */
    public function generate(array $params): ImageResponse
    {
        $response = $this->transport->request('POST', '/images', json: $params);
        $decoded = json_decode((string) $response->getBody(), true);
        $decoded = is_array($decoded) ? $decoded : [];
        if (isset($decoded['error'])) {
            throw new OpenRouterException('Image generation failed: ' . json_encode($decoded['error']));
        }

        return ImageResponse::fromArray($decoded, (string) ($params['model'] ?? ''));
    }

    /**
     * Try each model in order until one returns an image.
     *
     * @param string[] $models primary model first, then fallbacks
     * @param array<string, mixed> $params as generate(), without "model"
     * @throws OpenRouterException when every model fails (message lists each failure)
     */
    public function generateWithFallback(array $models, array $params): ImageResponse
    {
        $errors = [];
        foreach (array_values(array_unique(array_filter($models))) as $model) {
            try {
                $result = $this->generate(['model' => $model] + $params);
                $result->first();

                return $result;
            } catch (\Throwable $e) {
                $errors[] = "[$model] " . $e->getMessage();
            }
        }

        throw new OpenRouterException("All image models failed:\n" . implode("\n", $errors));
    }
}
