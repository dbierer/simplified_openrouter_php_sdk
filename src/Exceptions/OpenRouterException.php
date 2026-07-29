<?php

declare(strict_types=1);

namespace OpenRouter\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Base class for all exceptions raised by this SDK.
 */
class OpenRouterException extends RuntimeException
{
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
