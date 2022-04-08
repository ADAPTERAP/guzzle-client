<?php

namespace Adapterap\GuzzleClient\Exceptions;

use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

/**
 * HTTP коды 5xx.
 */
class ServerException extends GuzzleClientException implements ServerExceptionInterface
{
    // Nothing
}
