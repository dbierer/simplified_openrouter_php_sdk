<?php

declare(strict_types=1);

namespace OpenRouter\Http;

/**
 * Exponential-backoff retry policy, applied to connection failures and 5XX
 * responses. Defaults mirror the official Python SDK.
 *
 * $sleeper replaces the blocking usleep() between attempts. It receives the
 * delay in milliseconds, so an event loop or Fiber scheduler can suspend the
 * current task instead of pausing the whole process.
 */
final class RetryConfig
{
    public function __construct(
        public readonly int $maxAttempts = 4,
        public readonly int $initialIntervalMs = 500,
        public readonly int $maxIntervalMs = 60000,
        public readonly float $backoffMultiplier = 1.5,
        public readonly int $maxElapsedTimeMs = 3600000,
        public readonly ?\Closure $sleeper = null,
    ) {
    }

    public function delayForAttempt(int $attempt): int
    {
        $delay = (int) round($this->initialIntervalMs * ($this->backoffMultiplier ** $attempt));

        return min($delay, $this->maxIntervalMs);
    }
}
