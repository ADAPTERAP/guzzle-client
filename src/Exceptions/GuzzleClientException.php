<?php

namespace Adapterap\GuzzleClient\Exceptions;

use Adapterap\GuzzleClient\GuzzleClientResponse;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class GuzzleClientException extends RuntimeException implements Responsable
{
    /**
     * Ответ от сервера.
     *
     * @var GuzzleClientResponse
     */
    protected GuzzleClientResponse $response;

    /**
     * GuzzleClientException constructor.
     *
     * @param GuzzleClientResponse $response
     * @param null|string          $message
     */
    public function __construct(GuzzleClientResponse $response, ?string $message = null)
    {
        parent::__construct($message ?? "Server returns an {$response->getStatusCode()} HTTP status code");

        $this->response = $response;
    }

    /**
     * Преобразует исключение в ответ от сервера.
     *
     * @return ResponseInterface|GuzzleClientResponse
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param Request $request
     *
     * @throws TransportExceptionInterface
     * @return JsonResponse
     */
    public function toResponse($request): JsonResponse
    {
        return new JsonResponse([
            'message' => $this->getMessage(),
        ], $this->getResponse()->getStatusCode());
    }

    /**
     * Контекст ошибки.
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @return mixed[]
     */
    public function context(): array
    {
        return [
            'response' => $this->getResponse()->getContent(false),
        ];
    }

    /**
     * Отправляет информацию об ошибке клиенту.
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function report(): void
    {
        Log::error(
            $this->getMessage(),
            array_merge($this->context(), [
                'exception' => $this,
            ])
        );
    }
}
