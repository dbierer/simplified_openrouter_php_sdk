<?php

declare(strict_types=1);

namespace OpenRouter\Exceptions;

/**
 * Raised when a request could not be completed at all (e.g. connection
 * refused, DNS failure, timeout) even after retries were exhausted.
 */
class TransportException extends OpenRouterException
{
}
