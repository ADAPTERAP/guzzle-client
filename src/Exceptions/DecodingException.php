<?php

namespace Adapterap\GuzzleClient\Exceptions;

use Throwable;
use Adapterap\GuzzleClient\GuzzleClientResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;

class DecodingException extends GuzzleClientException implements DecodingExceptionInterface
{
    /**
     * DecodingException constructor.
     *
     * @param GuzzleClientResponse $response
     * @param Throwable            $throwable
     * @param null|string          $message
     */
    public function __construct(GuzzleClientResponse $response, private Throwable $throwable, ?string $message = null)
    {
        parent::__construct($response, $message ?? 'Не удалось привести ответ от сервера к массиву');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     *
     * @return array
     */
    public function context(): array
    {
        return [
            'content' => $this->response->getContent(false),
            'exception' => $this->throwable,
        ];
    }
}
