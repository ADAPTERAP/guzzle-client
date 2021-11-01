<?php

namespace Adapterap\GuzzleClient\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class UnprocessableEntityException extends GuzzleClientException
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws TransportExceptionInterface
     */
    public function toResponse($request): JsonResponse
    {
        return new JsonResponse([
            'message' => $this->getMessage(),
            'errors' => $this->getResponse()->toArray(false)['errors'] ?? [],
        ], $this->getResponse()->getStatusCode());
    }
}
