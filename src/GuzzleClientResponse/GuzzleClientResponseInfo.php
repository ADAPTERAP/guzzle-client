<?php

namespace Adapterap\GuzzleClient\GuzzleClientResponse;

use Carbon\CarbonInterface;
use Illuminate\Contracts\Support\Arrayable;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class GuzzleClientResponseInfo implements Arrayable
{
    /**
     * The error message when the transfer was aborted, null otherwise.
     *
     * @var null|string
     */
    public ?string $error = null;

    /**
     * The last response code or 0 when it is not known yet.
     *
     * @var int
     */
    public int $httpCode;

    /**
     * The HTTP verb of the last request.
     *
     * @var string
     */
    public string $httpMethod;

    /**
     * The number of redirects followed while executing the request.
     *
     * @var int
     */
    public int $redirectCount = 0;

    /**
     * The resolved location of redirect responses, null otherwise.
     *
     * @var null|string
     */
    public ?string $redirectUrl = null;

    /**
     * An array modelled after the special $http_response_header variable.
     *
     * @var array
     */
    public array $responseHeaders;

    /**
     * The time when the request was sent or 0.0 when it's pending.
     *
     * @var CarbonInterface
     */
    public CarbonInterface $startTime;

    /**
     * The last effective URL of the request.
     *
     * @var string
     */
    public string $url;

    /**
     * The value of the "user_data" request option, null if not set.
     *
     * @var null|array
     */
    public ?array $userData;

    /**
     * The content of the response.
     *
     * @var string
     */
    public string $content;

    /**
     * AliExpressApiClientResponse constructor.
     *
     * @param string               $method
     * @param string               $url
     * @param array                $options
     * @param PsrResponseInterface $response
     * @param CarbonInterface      $startTime
     * @param string               $content
     */
    public function __construct(
        string $method,
        string $url,
        array $options,
        PsrResponseInterface $response,
        CarbonInterface $startTime,
        string $content
    ) {
        $this->httpCode = $response->getStatusCode();
        $this->httpMethod = $method;
        $this->responseHeaders = $response->getHeaders();
        $this->startTime = $startTime;
        $this->url = $url;
        $this->userData = $options;
        $this->content = $content;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'error' => $this->error,
            'httpCode' => $this->httpCode,
            'httpMethod' => $this->httpMethod,
            'redirectCount' => $this->redirectCount,
            'redirectUrl' => $this->redirectUrl,
            'responseHeaders' => $this->responseHeaders,
            'startTime' => $this->startTime->toIso8601String(),
            'url' => $this->url,
            'userData' => $this->userData,
            'content' => $this->content,
        ];
    }
}
