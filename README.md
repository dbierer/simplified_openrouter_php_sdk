# openrouter_php_sdk

An unofficial PHP SDK for the [OpenRouter.ai](https://openrouter.ai) API, modeled after the
[official Python SDK](https://github.com/OpenRouterTeam/python-sdk). This is a hand-written,
idiomatic PHP client — not a generated 1:1 port — covering the core of the OpenRouter API:

- **Chat Completions** — including streaming
- **Models** — list, get, count
- **Endpoints** — list provider endpoints for a model
- **Generations** — request/usage metadata lookup
- **Credits** — account balance
- **API Keys** — full CRUD (management key required)
- **Embeddings**

The Python SDK is auto-generated from OpenRouter's OpenAPI spec and covers ~90 endpoint groups
(TTS/STT, video generation, OAuth, workspaces, BYOK, datasets, guardrails, analytics, and more).
This PHP SDK deliberately covers the subset most consumers need. The architecture (a `Transport`
class plus one resource class per endpoint group) makes it straightforward to add more resources
later if you need them — contributions welcome.

## Requirements

- PHP 8.1+
- [Composer](https://getcomposer.org)

## Installation

```bash
composer require dbierer/openrouter-php-sdk
```

## Quick start

```php
use OpenRouter\Client;
use OpenRouter\DTO\ChatMessage;

// Reads OPENROUTER_API_KEY from the environment if not passed explicitly.
$client = new Client(apiKey: 'sk-or-...');

$response = $client->chat->create([
    'model' => 'openai/gpt-4o-mini',
    'messages' => [
        ChatMessage::system('You are a helpful assistant.'),
        ChatMessage::user('Say hello in three languages.'),
    ],
]);

echo $response->getContent();
```

## Streaming

```php
$stream = $client->chat->createStreamed([
    'model' => 'openai/gpt-4o-mini',
    'messages' => [ChatMessage::user('Count to 5.')],
]);

foreach ($stream as $chunk) {
    echo $chunk->getContentDelta();
}
```

`createStreamed()` returns a `ChatCompletionStream`, an `IteratorAggregate` that lazily parses the
API's `text/event-stream` response and yields one `ChatCompletionChunk` per server-sent event,
stopping automatically at the `[DONE]` sentinel.

## Request parameters

`chat->create()` / `chat->createStreamed()` accept the request body as a plain associative array,
matching the [OpenRouter Chat Completions API](https://openrouter.ai/docs/api-reference/chat-completion)
directly (`model`, `messages`, `temperature`, `max_tokens`, `tools`, `tool_choice`,
`provider`, `reasoning`, `response_format`, etc.) — nothing is hidden behind a rigid DTO, so any
parameter the API supports can be passed through as-is. `messages` entries may be plain arrays or
`OpenRouter\DTO\ChatMessage` instances (`ChatMessage::system()`, `::user()`, `::assistant()`, `::tool()`).

## Resources

```php
// Models
$models = $client->models->list(['limit' => 20, 'category' => 'programming']);
foreach ($models as $model) {
    echo "{$model->id}: {$model->contextLength} tokens\n";
}
$model = $client->models->get('openai', 'gpt-4o');
$count = $client->models->count();

// Endpoints (which providers serve a given model)
$endpoints = $client->endpoints->forModel('openai', 'gpt-4o'); // raw decoded array

// Generations
$generation = $client->generations->get('gen-abc123');
echo $generation->totalCost;

// Credits (requires a management API key)
$credits = $client->credits->get();
echo $credits->getRemaining();

// API keys (requires a management API key)
$key = $client->apiKeys->create(['name' => 'my-app-key', 'limit' => 10.0]);
echo $key->key; // plaintext key — only ever available on the create() response
$client->apiKeys->update($key->hash, ['disabled' => true]);
$client->apiKeys->delete($key->hash);
foreach ($client->apiKeys->list() as $k) { /* ... */ }

// Embeddings
$result = $client->embeddings->create([
    'model' => 'openai/text-embedding-3-small',
    'input' => 'Hello, world!',
]);
$vector = $result->data[0]->vector;
```

See `examples/` for complete runnable scripts.

## Configuration

```php
use OpenRouter\Client;
use OpenRouter\Http\RetryConfig;

$client = new Client(
    apiKey: 'sk-or-...',
    baseUrl: 'https://openrouter.ai/api/v1', // e.g. https://eu.openrouter.ai/api/v1 for EU in-region routing
    httpReferer: 'https://myapp.example',    // sent as HTTP-Referer, used for OpenRouter app rankings
    title: 'My App',                        // sent as X-Title
    timeoutSeconds: 60.0,
    retryConfig: new RetryConfig(maxAttempts: 4, initialIntervalMs: 500, maxIntervalMs: 60000),
);
```

You can also inject your own Guzzle client (e.g. with custom middleware or a mock handler for
testing) via the `httpClient` constructor argument.

## Error handling

Requests that receive an HTTP error status throw a subclass of `OpenRouter\Exceptions\ApiException`,
mapped by status code (`BadRequestException`, `UnauthorizedException`, `PaymentRequiredException`,
`ForbiddenException`, `NotFoundException`, `TooManyRequestsException`, `InternalServerException`, etc.):

```php
use OpenRouter\Exceptions\ApiException;

try {
    $client->chat->create(['model' => 'openai/gpt-4o', 'messages' => [...]]);
} catch (ApiException $e) {
    echo $e->getStatusCode();  // e.g. 429
    echo $e->getMessage();     // the API's error message
    echo $e->getMetadata();    // provider-specific error metadata, if any
}
```

Connection-level failures (DNS, timeouts, refused connections) that persist after retries are
exhausted throw `OpenRouter\Exceptions\TransportException` instead.

5xx responses are retried automatically with exponential backoff (defaults: up to 4 attempts,
starting at 500ms, capped at 60s, 1.5x multiplier) before an exception is raised.

## Development

```bash
composer install
composer test
```

## License

MIT. Not affiliated with or endorsed by OpenRouter.
