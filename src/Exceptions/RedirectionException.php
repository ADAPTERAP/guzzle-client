<?php

namespace Adapterap\GuzzleClient\Exceptions;

use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;

/**
 * HTTP коды 3xx
 */
class RedirectionException extends GuzzleClientException implements RedirectionExceptionInterface
{
    // Nothing
}
