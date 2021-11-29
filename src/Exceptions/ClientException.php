<?php

namespace Adapterap\GuzzleClient\Exceptions;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

/**
 * HTTP коды 4xx
 */
class ClientException extends GuzzleClientException implements ClientExceptionInterface
{
    // Nothing
}
