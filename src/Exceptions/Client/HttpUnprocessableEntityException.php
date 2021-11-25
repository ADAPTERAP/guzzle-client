<?php

namespace Adapterap\GuzzleClient\Exceptions\Client;

use Adapterap\GuzzleClient\Exceptions\ClientException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Ошибка 422
 */
class HttpUnprocessableEntityException extends ClientException
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function toResponse($request): JsonResponse
    {
        return new JsonResponse([
            'message' => $this->getMessage(),
            'errors' => $this->getResponse()->toArray(false)['errors'] ?? [],
        ], $this->getResponse()->getStatusCode());
    }
}
