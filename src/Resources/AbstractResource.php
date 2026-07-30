<?php
namespace OpenRouter\Resources;

use OpenRouter\Http\Transport;

abstract class AbstractResource
{
    public function __construct(protected readonly Transport $transport)
    {
    }
}
