<?php

namespace Adapterap\GuzzleClient\Exceptions;

use Throwable;
use Adapterap\GuzzleClient\GuzzleClientResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;

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
     * Report about the problem.
     */
    public function report(): void
    {
        Log::info($this->getMessage(), [
            'context' => $this->response->getContent(false),
            'exception' => $this->throwable,
        ]);
    }
}
