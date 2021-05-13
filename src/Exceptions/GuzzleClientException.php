<?php

namespace Adapterap\GuzzleClient\Exceptions;

use Adapterap\GuzzleClient\GuzzleClientResponse;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
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
     * @return ResponseInterface
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
     * @return JsonResponse
     */
    public function toResponse($request): JsonResponse
    {
        return new JsonResponse([
            'message' => $this->getMessage(),
        ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}
