<?php

namespace Adapterap\GuzzleClient\Exceptions;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

class ClientException extends GuzzleClientException implements ClientExceptionInterface
{
    // Nothing
}
