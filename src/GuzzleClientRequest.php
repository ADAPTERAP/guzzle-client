<?php

namespace Adapterap\GuzzleClient;

use Adapterap\GuzzleClient\Exceptions\GuzzleClientException;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use JsonException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface as SymfonyResponseInterface;
use Throwable;

/**
 * Class GuzzleClientRequest.
 *
 * @template T of GuzzleClientResponse.
 */
class GuzzleClientRequest
{
    /**
     * HTTP клиент.
     *
     * @var Client
     */
    protected Client $client;

    /**
     * Имя класса, который будет обрабатывать ответ от сервера.
     *
     * @var class-string<T>|string
     */
    protected string $responseClassName;

    /**
     * Глобальные заголовки.
     *
     * @var array<string, mixed>
     */
    protected array $headers = [];

    /**
     * Хост сервера, на который будут посылаться запросы.
     *
     * @var string
     */
    protected string $baseUri;

    /**
     * True, если включен режим отладки.
     *
     * @var bool
     */
    protected static bool $debug = false;

    /**
     * GuzzleClientRequest constructor.
     */
    public function __construct(string $baseUri)
    {
        $this->baseUri = $baseUri;
        $this->client = $this->getClient();
    }

    /**
     * Запрашивает ответ в виде JSON у сервера.
     *
     * @return $this
     */
    public function acceptJson(): GuzzleClientRequest
    {
        $this->headers['Accept'] = 'application/json';

        return $this;
    }

    /**
     * Create and send an HTTP GET request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well.
     *
     * @param string               $url
     * @param array<string, mixed> $options
     *
     * @throws GuzzleException
     *
     * @return GuzzleClientResponse|T
     */
    public function get(string $url, array $options = []): GuzzleClientResponse
    {
        return $this->request('GET', $url, $options);
    }

    /**
     * Create and send an HTTP POST request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well.
     *
     * @param string                 $url
     * @param array<string, mixed[]> $options
     *
     * @throws GuzzleException
     *
     * @return GuzzleClientResponse|T
     */
    public function post(string $url, array $options = []): GuzzleClientResponse
    {
        return $this->request('POST', $url, $options);
    }

    /**
     * Create and send an HTTP PUT request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well.
     *
     * @param string                 $url
     * @param array<string, mixed[]> $options
     *
     * @throws GuzzleException
     *
     * @return GuzzleClientResponse|T
     */
    public function put(string $url, array $options = []): GuzzleClientResponse
    {
        return $this->request('PUT', $url, $options);
    }

    /**
     * Create and send an HTTP DELETE request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well.
     *
     * @param string                 $url
     * @param array<string, mixed[]> $options
     *
     * @throws GuzzleException
     *
     * @return GuzzleClientResponse|T
     */
    public function delete(string $url, array $options = []): GuzzleClientResponse
    {
        return $this->request('DELETE', $url, $options);
    }

    /**
     * Create and send an HTTP PATCH request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well.
     *
     * @param string                 $url
     * @param array<string, mixed[]> $options
     *
     * @throws GuzzleException
     *
     * @return GuzzleClientResponse|T
     */
    public function patch(string $url, array $options = []): GuzzleClientResponse
    {
        return $this->request('PATCH', $url, $options);
    }

    /**
     * Create and send an HTTP HEAD request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well.
     *
     * @param string                 $url
     * @param array<string, mixed[]> $options
     *
     * @throws GuzzleException
     *
     * @return GuzzleClientResponse|T
     */
    public function head(string $url, array $options = []): GuzzleClientResponse
    {
        return $this->request('HEAD', $url, $options);
    }

    /**
     * Create and send an CONNECT HTTP request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     *
     * @param string                 $url
     * @param array<string, mixed[]> $options
     *
     * @throws GuzzleException
     *
     * @return GuzzleClientResponse|T
     */
    public function connect(string $url, array $options = []): GuzzleClientResponse
    {
        return $this->request('CONNECT', $url, $options);
    }

    /**
     * Create and send an OPTIONS HTTP request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     *
     * @param string                 $url
     * @param array<string, mixed[]> $options
     *
     * @throws GuzzleException
     *
     * @return GuzzleClientResponse|T
     */
    public function options(string $url, array $options = []): GuzzleClientResponse
    {
        return $this->request('OPTIONS', $url, $options);
    }

    /**
     * Create and send an HTTP request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well.
     *
     * @param string               $method  HTTP method
     * @param string               $url
     * @param array<string, mixed> $options Request options to apply. See \GuzzleHttp\RequestOptions.
     *
     * @throws GuzzleException
     * @throws Throwable
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     *
     * @return GuzzleClientResponse|T
     */
    public function request(string $method, string $url, array $options = []): GuzzleClientResponse
    {
        $start = Carbon::now();

        $options[RequestOptions::HEADERS] = array_merge(
            $options[RequestOptions::HEADERS] ?? [],
            $this->headers
        );

        $response = $this->client->request($method, $url, $options);

        try {
            /** @var GuzzleClientResponse $result */
            $result = new $this->responseClassName(
                $method,
                $url,
                $options,
                $response,
                $start
            );

            $this->logging($url, $method, $options, $response, $result);

            if ($result->isStream() === false) {
                $result->readContentsFromStream();
            }

            return $result;
        } catch (GuzzleClientException $exception) {
            $this->logging($url, $method, $options, $response, $exception->getResponse());

            throw $exception;
        } catch (Throwable $throwable) {
            $this->logging($url, $method, $options, $response);

            throw $throwable;
        } finally {
            // Закрываем соединение
            if (!array_key_exists(RequestOptions::STREAM, $options) || $options[RequestOptions::STREAM] !== true) {
                $response->getBody()->close();
            }
        }
    }

    /**
     * Сеттер для client.
     *
     * @param Client $client
     *
     * @return $this
     */
    public function setClient(Client $client): GuzzleClientRequest
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Включает/отключает режим отладки.
     *
     * @param bool $value
     *
     * @return $this
     *
     * @deprecated
     */
    public function enableDebug(bool $value): self
    {
        self::debug($value);

        return $this;
    }

    /**
     * Включает/отключает режим отладки.
     *
     * @param bool $value
     */
    public static function debug(bool $value = true): void
    {
        self::$debug = $value;
    }

    /**
     * Возвращает клиент для работы с HTTP.
     *
     * @return Client
     */
    protected function getClient(): Client
    {
        return new Client([
            'base_uri' => $this->baseUri,
            RequestOptions::VERIFY => false,
            RequestOptions::HTTP_ERRORS => false,
        ]);
    }

    /**
     * Логирует запрос и ответ.
     *
     * @param string                                             $url
     * @param string                                             $method
     * @param mixed[]                                            $options
     * @param PsrResponseInterface                               $originalResponse
     * @param null|GuzzleClientResponse|SymfonyResponseInterface $response
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function logging(
        string $url,
        string $method,
        array $options,
        PsrResponseInterface $originalResponse,
        $response = null
    ): void {
        if (self::$debug === false) {
            return;
        }

        if ($response) {
            try {
                $content = $response->toArray(false);
            } catch (JsonException $exception) {
                $content = $response->getContent(false);
            }
        } else {
            $content = (string) $originalResponse->getBody()->getContents();
        }

        $data = [
            'request' => [
                'base_uri' => $this->baseUri,
                'path' => $url,
                'method' => $method,
                'options' => $options,
            ],
            'response' => [
                'status' => $response
                    ? $response->getStatusCode()
                    : $originalResponse->getStatusCode(),
                'headers' => $response
                    ? $response->getHeaders(false)
                    : $originalResponse->getHeaders(),
                'content' => $content,
            ],
        ];

        // Не передаем $data вторым параметром потому, что иногда json'ы могут быть глубокими,
        // а Monolog умеет работать лишь с 9ю уровнями вложенности:
        // Over 9 levels deep, aborting normalization
        Log::debug(static::class . ' debug' . json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
