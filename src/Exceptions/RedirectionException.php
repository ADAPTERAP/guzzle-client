<?php

namespace Adapterap\GuzzleClient\Exceptions;

use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;

class RedirectionException extends GuzzleClientException implements RedirectionExceptionInterface
{
    // Nothing
}
