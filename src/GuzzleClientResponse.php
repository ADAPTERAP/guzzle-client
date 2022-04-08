<?php

namespace Adapterap\GuzzleClient;

use Adapterap\GuzzleClient\Exceptions\Client\HttpBadRequestException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpConflictException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpEnhanceYourCalmException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpExpectationFailedException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpFailedDependencyException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpForbiddenException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpGoneException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpIAmATeapotException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpLengthRequiredException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpLockedException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpMethodNotAllowedException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpMisdirectedRequestException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpNotAcceptableException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpNotFoundException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpPaymentRequiredException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpPreconditionFailedException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpPreconditionRequiredException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpProxyAuthenticationRequiredException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpRequestedRangeNotSatisfiableException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpRequestEntityTooLargeException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpRequestHeaderFieldsTooLargeException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpRequestTimeoutException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpRequestUriTooLongException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpTooEarlyException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpTooManyRequestsException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpUnauthorizedException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpUnavailableForLegalReasonsException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpUnprocessableEntityException;
use Adapterap\GuzzleClient\Exceptions\Client\HttpUnsupportedMediaType;
use Adapterap\GuzzleClient\Exceptions\Client\HttpUpgradeRequiredException;
use Adapterap\GuzzleClient\Exceptions\ClientException;
use Adapterap\GuzzleClient\Exceptions\DecodingException;
use Adapterap\GuzzleClient\Exceptions\GuzzleClientException;
use Adapterap\GuzzleClient\Exceptions\MethodNotSupportedException;
use Adapterap\GuzzleClient\Exceptions\RedirectionException;
use Adapterap\GuzzleClient\Exceptions\ServerException;
use Adapterap\GuzzleClient\GuzzleClientResponse\GuzzleClientResponseInfo;
use Carbon\CarbonInterface;
use GuzzleHttp\RequestOptions;
use JsonException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GuzzleClientResponse implements ResponseInterface
{
    /**
     * HTTP метод запроса.
     *
     * @var string
     */
    private string $method;

    /**
     * URL/path запроса.
     *
     * @var string
     */
    private string $url;

    /**
     * Настройки/параметры запроса.
     *
     * @var array<string, mixed>
     */
    private array $options;

    /**
     * Ответ сервера.
     *
     * @var PsrResponseInterface
     */
    private PsrResponseInterface $response;

    /**
     * Дата и время когда запрос был отправлен.
     *
     * @var CarbonInterface
     */
    private CarbonInterface $startTime;

    /**
     * Содержимое ответа.
     *
     * @var string
     */
    private string $content;

    /**
     * GuzzleClientResponse constructor.
     *
     * @param string               $method
     * @param string               $url
     * @param array<string, mixed> $options
     * @param PsrResponseInterface $response
     * @param CarbonInterface      $startTime
     */
    public function __construct(
        string $method,
        string $url,
        array $options,
        PsrResponseInterface $response,
        CarbonInterface $startTime
    ) {
        $this->method = $method;
        $this->url = $url;
        $this->options = $options;
        $this->response = $response;
        $this->startTime = $startTime;

        if (array_key_exists(RequestOptions::STREAM, $options) && $options[RequestOptions::STREAM] === true) {
            return;
        }

        $content = $this->response->getBody()->getContents();
        $this->content = (string) str_replace(' ', ' ', $content);
    }

    /**
     * Gets the HTTP status code of the response.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * Gets the HTTP status text of the response.
     *
     * @return string
     */
    public function getStatusText(): string
    {
        return $this->response->getReasonPhrase();
    }

    /**
     * Gets the HTTP headers of the response.
     *
     * @param bool $throw Whether an exception should be thrown on 3/4/5xx status codes
     *
     * @throws ServerExceptionInterface      On a 5xx when $throw is true
     * @throws RedirectionExceptionInterface On a 3xx when $throw is true and the "max_redirects" option has been reached
     * @throws ClientExceptionInterface      On a 4xx when $throw is true
     *
     * @return string[][] The headers of the response keyed by header names in lowercase
     */
    public function getHeaders(bool $throw = true): array
    {
        $this->throwAnExceptionIfNeed($throw);

        return $this->response->getHeaders();
    }

    /**
     * Gets the response body as a string.
     *
     * @param bool $throw Whether an exception should be thrown on 3/4/5xx status codes
     *
     * @throws ServerExceptionInterface      On a 5xx when $throw is true
     * @throws RedirectionExceptionInterface On a 3xx when $throw is true and the "max_redirects" option has been reached
     * @throws ClientExceptionInterface      On a 4xx when $throw is true
     *
     * @return string
     */
    public function getContent(bool $throw = true): string
    {
        $this->throwAnExceptionIfNeed($throw);

        return $this->content;
    }

    /**
     * Gets the response body as a stream.
     *
     * @param bool $throw Whether an exception should be thrown on 3/4/5xx status codes
     *
     * @throws ServerExceptionInterface On a 5xx when $throw is true
     * @throws ClientExceptionInterface On a 4xx when $throw is true
     *
     * @return StreamInterface
     */
    public function getBody(bool $throw = true): StreamInterface
    {
        $this->throwAnExceptionIfNeed($throw);

        return $this->response->getBody();
    }

    /**
     * Gets the response body decoded as array, typically from a JSON payload.
     *
     * @param bool $throw Whether an exception should be thrown on 3/4/5xx status codes
     *
     * @throws ClientExceptionInterface      On a 4xx when $throw is true
     * @throws ServerExceptionInterface      On a 5xx when $throw is true
     * @throws DecodingExceptionInterface    When the body cannot be decoded to an array
     * @throws RedirectionExceptionInterface On a 3xx when $throw is true and the "max_redirects" option has been reached
     *
     * @return mixed[]
     */
    public function toArray(bool $throw = true): array
    {
        try {
            return json_decode($this->getContent($throw), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            if ($throw) {
                throw new DecodingException($this, $exception);
            }

            return [];
        }
    }

    /**
     * Closes the response stream and all related buffers.
     *
     * No further chunk will be yielded after this method has been called.
     */
    public function cancel(): void
    {
        throw new MethodNotSupportedException(static::class . '::' . __FUNCTION__);
    }

    /**
     * Returns info coming from the transport layer.
     *
     * This method SHOULD NOT throw any ExceptionInterface and SHOULD be non-blocking.
     * The returned info is "live": it can be empty and can change from one call to
     * another, as the request/response progresses.
     *
     * The following info MUST be returned:
     *  - canceled (bool) - true if the response was canceled using ResponseInterface::cancel(), false otherwise
     *  - error (string|null) - the error message when the transfer was aborted, null otherwise
     *  - http_code (int) - the last response code or 0 when it is not known yet
     *  - http_method (string) - the HTTP verb of the last request
     *  - redirect_count (int) - the number of redirects followed while executing the request
     *  - redirect_url (string|null) - the resolved location of redirect responses, null otherwise
     *  - response_headers (array) - an array modelled after the special $http_response_header variable
     *  - start_time (float) - the time when the request was sent or 0.0 when it's pending
     *  - url (string) - the last effective URL of the request
     *  - user_data (mixed|null) - the value of the "user_data" request option, null if not set
     *
     * When the "capture_peer_cert_chain" option is true, the "peer_certificate_chain"
     * attribute SHOULD list the peer certificates as an array of OpenSSL X.509 resources.
     *
     * Other info SHOULD be named after curl_getinfo()'s associative return value.
     *
     * @return null|array|GuzzleClientResponseInfo|mixed An array of all available info, or one of them when $type is
     *                                                   provided, or null when an unsupported type is requested
     */
    public function getInfo(string $type = null)
    {
        $result = new GuzzleClientResponseInfo(
            $this->method,
            $this->url,
            $this->options,
            $this->response,
            $this->startTime,
            $this->content
        );

        if ($type) {
            return $result->toArray()[$type];
        }

        return $result;
    }

    /**
     * Проверяет HTTP код и если разработчик просит бросить исключение - выбрасывает.
     *
     * @param bool $throw Whether an exception should be thrown on 3/4/5xx status codes
     *
     * @throws ClientExceptionInterface|GuzzleClientException
     */
    public function throwAnExceptionIfNeed(bool $throw = true): void
    {
        if ($throw) {
            switch ($this->getStatusCode()) {
                // 4xx
                case Response::HTTP_BAD_REQUEST:
                    throw new HttpBadRequestException($this);
                case Response::HTTP_UNAUTHORIZED:
                    throw new HttpUnauthorizedException($this);
                case Response::HTTP_PAYMENT_REQUIRED:
                    throw new HttpPaymentRequiredException($this);
                case Response::HTTP_FORBIDDEN:
                    throw new HttpForbiddenException($this);
                case Response::HTTP_NOT_FOUND:
                    throw new HttpNotFoundException($this);
                case Response::HTTP_METHOD_NOT_ALLOWED:
                    throw new HttpMethodNotAllowedException($this);
                case Response::HTTP_NOT_ACCEPTABLE:
                    throw new HttpNotAcceptableException($this);
                case Response::HTTP_PROXY_AUTHENTICATION_REQUIRED:
                    throw new HttpProxyAuthenticationRequiredException($this);
                case Response::HTTP_REQUEST_TIMEOUT:
                    throw new HttpRequestTimeoutException($this);
                case Response::HTTP_CONFLICT:
                    throw new HttpConflictException($this);
                case Response::HTTP_GONE:
                    throw new HttpGoneException($this);
                case Response::HTTP_LENGTH_REQUIRED:
                    throw new HttpLengthRequiredException($this);
                case Response::HTTP_PRECONDITION_FAILED:
                    throw new HttpPreconditionFailedException($this);
                case Response::HTTP_REQUEST_ENTITY_TOO_LARGE:
                    throw new HttpRequestEntityTooLargeException($this);
                case Response::HTTP_REQUEST_URI_TOO_LONG:
                    throw new HttpRequestUriTooLongException($this);
                case Response::HTTP_UNSUPPORTED_MEDIA_TYPE:
                    throw new HttpUnsupportedMediaType($this);
                case Response::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE:
                    throw new HttpRequestedRangeNotSatisfiableException($this);
                case Response::HTTP_EXPECTATION_FAILED:
                    throw new HttpExpectationFailedException($this);
                case Response::HTTP_I_AM_A_TEAPOT:
                    throw new HttpIAmATeapotException($this);
                case 420:
                    throw new HttpEnhanceYourCalmException($this);
                case Response::HTTP_MISDIRECTED_REQUEST:
                    throw new HttpMisdirectedRequestException($this);
                case Response::HTTP_UNPROCESSABLE_ENTITY:
                    throw new HttpUnprocessableEntityException($this, $this->toArray(false)['message'] ?? null);
                case Response::HTTP_LOCKED:
                    throw new HttpLockedException($this);
                case Response::HTTP_FAILED_DEPENDENCY:
                    throw new HttpFailedDependencyException($this);
                case Response::HTTP_TOO_EARLY:
                    throw new HttpTooEarlyException($this);
                case Response::HTTP_UPGRADE_REQUIRED:
                    throw new HttpUpgradeRequiredException($this);
                case Response::HTTP_PRECONDITION_REQUIRED:
                    throw new HttpPreconditionRequiredException($this);
                case Response::HTTP_TOO_MANY_REQUESTS:
                    throw new HttpTooManyRequestsException($this);
                case Response::HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE:
                    throw new HttpRequestHeaderFieldsTooLargeException($this);
                case Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS:
                    throw new HttpUnavailableForLegalReasonsException($this);
            }
        }

        if ($throw && $this->getStatusCode() >= Response::HTTP_INTERNAL_SERVER_ERROR) {
            throw new ServerException($this);
        }

        if ($throw && $this->getStatusCode() >= Response::HTTP_BAD_REQUEST) {
            throw new ClientException($this);
        }

        if ($throw && $this->getStatusCode() >= Response::HTTP_MULTIPLE_CHOICES) {
            throw new RedirectionException($this);
        }
    }
}
