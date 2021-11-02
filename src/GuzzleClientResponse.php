<?php

namespace Adapterap\GuzzleClient;

use Adapterap\GuzzleClient\Exceptions\ClientException;
use Adapterap\GuzzleClient\Exceptions\DecodingException;
use Adapterap\GuzzleClient\Exceptions\ForbiddenException;
use Adapterap\GuzzleClient\Exceptions\MethodNotSupportedException;
use Adapterap\GuzzleClient\Exceptions\NotFoundException;
use Adapterap\GuzzleClient\Exceptions\RedirectionException;
use Adapterap\GuzzleClient\Exceptions\ServerException;
use Adapterap\GuzzleClient\Exceptions\UnauthorizedException;
use Adapterap\GuzzleClient\Exceptions\UnprocessableEntityException;
use Adapterap\GuzzleClient\GuzzleClientResponse\GuzzleClientResponseInfo;
use Carbon\CarbonInterface;
use JsonException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
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
     * @var array
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
     * @param string $method
     * @param string $url
     * @param array $options
     * @param PsrResponseInterface $response
     * @param CarbonInterface $startTime
     */
    public function __construct(
        string               $method,
        string               $url,
        array                $options,
        PsrResponseInterface $response,
        CarbonInterface      $startTime
    )
    {
        $this->method = $method;
        $this->url = $url;
        $this->options = $options;
        $this->response = $response;
        $this->startTime = $startTime;

        $content = $this->response->getBody()->getContents();
        $this->content = (string)str_replace(' ', ' ', $content);
    }

    /**
     * Gets the HTTP status code of the response.
     */
    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * Gets the HTTP headers of the response.
     *
     * @param bool $throw Whether an exception should be thrown on 3/4/5xx status codes
     *
     * @return string[][] The headers of the response keyed by header names in lowercase
     * @throws ClientExceptionInterface      On a 4xx when $throw is true
     * @throws ServerExceptionInterface      On a 5xx when $throw is true
     *
     * @throws RedirectionExceptionInterface On a 3xx when $throw is true and the "max_redirects" option has been reached
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
     * @return string
     * @throws ClientExceptionInterface      On a 4xx when $throw is true
     * @throws ServerExceptionInterface      On a 5xx when $throw is true
     *
     * @throws RedirectionExceptionInterface On a 3xx when $throw is true and the "max_redirects" option has been reached
     */
    public function getContent(bool $throw = true): string
    {
        $this->throwAnExceptionIfNeed($throw);

        return $this->content;
    }

    /**
     * Gets the response body decoded as array, typically from a JSON payload.
     *
     * @param bool $throw Whether an exception should be thrown on 3/4/5xx status codes
     *
     * @return array
     * @throws RedirectionExceptionInterface On a 3xx when $throw is true and the "max_redirects" option has been reached
     * @throws ClientExceptionInterface      On a 4xx when $throw is true
     * @throws ServerExceptionInterface      On a 5xx when $throw is true
     *
     * @throws DecodingExceptionInterface    When the body cannot be decoded to an array
     */
    public function toArray(bool $throw = true): array
    {
        try {
            return json_decode($this->getContent($throw), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            if ($throw) {
                throw new DecodingException($this);
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
     * @throws RedirectionExceptionInterface On a 3xx when $throw is true and the "max_redirects" option has been reached
     * @throws ClientExceptionInterface      On a 4xx when $throw is true
     * @throws UnauthorizedException         On a 401 when $throw is true
     * @throws ForbiddenException            On a 403 when $throw is true
     * @throws NotFoundException             On a 404 when $throw is true
     * @throws UnprocessableEntityException  On a 404 when $throw is true
     * @throws ServerExceptionInterface      On a 5xx when $throw is true
     */
    public function throwAnExceptionIfNeed(bool $throw = true): void
    {
        if ($throw) {
            switch ($this->getStatusCode()) {
                case Response::HTTP_UNAUTHORIZED:
                    throw new UnauthorizedException($this);
                case Response::HTTP_FORBIDDEN:
                    throw new ForbiddenException($this);
                case Response::HTTP_NOT_FOUND:
                    throw new NotFoundException($this);
                case Response::HTTP_UNPROCESSABLE_ENTITY:
                    throw new UnprocessableEntityException($this, $this->toArray(false)['message'] ?? null);
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
