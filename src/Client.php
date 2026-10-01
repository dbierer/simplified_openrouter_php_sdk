<?php
declare(strict_types=1);
namespace OpenRouter;

use GuzzleHttp\ClientInterface;
use OpenRouter\Http\RetryConfig;
use OpenRouter\Http\Transport;
use OpenRouter\Resources\ApiKeysResource;
use OpenRouter\Resources\ChatResource;
use OpenRouter\Resources\CreditsResource;
use OpenRouter\Resources\EmbeddingsResource;
use OpenRouter\Resources\EndpointsResource;
use OpenRouter\Resources\GenerationsResource;
use OpenRouter\Resources\ImagesResource;
use OpenRouter\Resources\SpeechResource;
use OpenRouter\Resources\ModelsResource;

/**
 * Entry point for the OpenRouter PHP SDK.
 *
 * ```php
 * $client = new Client(apiKey: getenv('OPENROUTER_API_KEY'));
 * $response = $client->chat->create([
 *     'model' => 'openai/gpt-4o',
 *     'messages' => [ChatMessage::user('Hello!')],
 * ]);
 * echo $response->getContent();
 * ```
 */
final class Client
{
    public readonly ChatResource $chat;
    public readonly ModelsResource $models;
    public readonly EndpointsResource $endpoints;
    public readonly GenerationsResource $generations;
    public readonly CreditsResource $credits;
    public readonly ApiKeysResource $apiKeys;
    public readonly EmbeddingsResource $embeddings;
    public readonly ImagesResource $images;
    public readonly SpeechResource $speech;

    /**
     * @param string $apiKey Your OpenRouter API key. Falls back to the
     *     OPENROUTER_API_KEY environment variable when not provided.
     * @param string $baseUrl Override the API base URL (e.g. to point at the
     *     EU in-region endpoint, https://eu.openrouter.ai/api/v1).
     * @param string|null $httpReferer Sent as the HTTP-Referer header; used by
     *     OpenRouter to attribute usage to your app for rankings.
     * @param string|null $title Sent as the X-Title header; your app's display name.
     */
    public function __construct(
        ?string $apiKey = null,
        string $baseUrl = 'https://openrouter.ai/api/v1',
        ?string $httpReferer = null,
        ?string $title = null,
        float $timeoutSeconds = 60.0,
        RetryConfig $retryConfig = new RetryConfig(),
        ?ClientInterface $httpClient = null,
    ) {
        $apiKey ??= getenv('OPENROUTER_API_KEY') ?: null;

        if ($apiKey === null || $apiKey === '') {
            throw new \InvalidArgumentException(
                'An OpenRouter API key is required. Pass it explicitly or set the OPENROUTER_API_KEY environment variable.',
            );
        }

        $transport = new Transport(
            apiKey: $apiKey,
            baseUrl: $baseUrl,
            httpReferer: $httpReferer,
            xTitle: $title,
            timeoutSeconds: $timeoutSeconds,
            retryConfig: $retryConfig,
            client: $httpClient,
        );

        $this->chat        = new ChatResource($transport);
        $this->models      = new ModelsResource($transport);
        $this->endpoints   = new EndpointsResource($transport);
        $this->generations = new GenerationsResource($transport);
        $this->credits     = new CreditsResource($transport);
        $this->apiKeys     = new ApiKeysResource($transport);
        $this->embeddings  = new EmbeddingsResource($transport);
        $this->images      = new ImagesResource($transport);
        $this->speech      = new SpeechResource($transport);
    }
}
