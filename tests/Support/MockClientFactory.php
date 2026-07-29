<?php

declare(strict_types=1);

namespace OpenRouter\Tests\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

final class MockClientFactory
{
    /** @var RequestInterface[] */
    public array $history = [];

    private MockHandler $mockHandler;
    public readonly Client $guzzle;

    /**
     * @param Response[] $responses
     */
    public function __construct(array $responses)
    {
        $this->mockHandler = new MockHandler($responses);
        $stack = HandlerStack::create($this->mockHandler);
        $stack->push(Middleware::history($this->history));

        $this->guzzle = new Client(['handler' => $stack]);
    }

    public function append(Response $response): void
    {
        $this->mockHandler->append($response);
    }

    public function lastRequest(): ?RequestInterface
    {
        $last = end($this->history);

        return $last === false ? null : $last['request'];
    }
}
