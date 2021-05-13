<?php

namespace Adapterap\GuzzleClient\Exceptions;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class MethodNotSupportedException extends RuntimeException implements Responsable
{
    /**
     * MethodDoesNotSupportException constructor.
     *
     * @param string $method
     */
    public function __construct(string $method)
    {
        parent::__construct("Method [{$method}] not supported");
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function toResponse($request): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Internal Error',
        ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}
