<?php

declare(strict_types=1);

namespace OpenRouter\Resources;

use OpenRouter\Http\Transport;

abstract class AbstractResource
{
    public function __construct(protected readonly Transport $transport)
    {
    }
}
