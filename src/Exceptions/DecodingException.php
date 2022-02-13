<?php

namespace Adapterap\GuzzleClient\Exceptions;

use Throwable;
use Adapterap\GuzzleClient\GuzzleClientResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Throwable;

class DecodingException extends GuzzleClientException implements DecodingExceptionInterface
{
    /**
     * Оригинальная ошибка.
     *
     * @var Throwable
     */
    private Throwable $throwable;

    /**
     * DecodingException constructor.
     *
     * @param GuzzleClientResponse $response
     * @param Throwable            $throwable
     * @param null|string          $message
     */
    public function __construct(GuzzleClientResponse $response, Throwable $throwable, ?string $message = null)
    {
        parent::__construct($response, $message ?? 'Не удалось привести ответ от сервера к массиву');

        $this->throwable = $throwable;
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
