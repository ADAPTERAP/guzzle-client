<?php

namespace Adapterap\GuzzleClient\Exceptions;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * HTTP коды 4xx.
 */
class ClientException extends GuzzleClientException implements ClientExceptionInterface
{
    /**
     * Контекст ошибки.
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @return array
     */
    public function context(): array
    {
        return array_merge(parent::context(), [
            'request' => [
                'method' => $this->response->getMethod(),
                'url' => $this->response->getUrl(),
                'options' => $this->response->getRequestOptions(),
            ],
        ]);
    }
}
