<?php

namespace Adapterap\GuzzleClient\Exceptions;

use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

class ServerException extends GuzzleClientException implements ServerExceptionInterface
{
    // Nothing
}
