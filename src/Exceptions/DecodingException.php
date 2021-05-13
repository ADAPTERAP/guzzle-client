<?php

namespace Adapterap\GuzzleClient\Exceptions;

use Adapterap\GuzzleClient\GuzzleClientResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;

class DecodingException extends GuzzleClientException implements DecodingExceptionInterface
{
    /**
     * DecodingException constructor.
     *
     * @param GuzzleClientResponse $response
     * @param null|string          $message
     */
    public function __construct(GuzzleClientResponse $response, ?string $message = null)
    {
        parent::__construct($response, $message ?? 'Не удалось привести ответ от сервера к массиву');
    }

    /**
     * Report about the problem.
     */
    public function report(): void
    {
        Log::info($this->getMessage(), [
            'content' => $this->response->getContent(false),
        ]);
    }
}
