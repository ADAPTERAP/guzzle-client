<?php

namespace Adapterap\GuzzleClient;

use Adapterap\GuzzleClient\Exceptions\GuzzleClientException;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

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
     * @var string|T
     */
    protected string $responseClassName;

    /**
     * Глобальные заголовки.
     *
     * @var array
     */
    protected array $headers = [];

    /**
     * Хост сервера, на который будут посылаться запросы.
     *
     * @var string
     */
    protected string $baseUri;

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
     * Create and send an GET HTTP request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     *
     * @param string $url
     * @param array $options
     *
     * @return GuzzleClientResponse|T
     * @throws GuzzleException
     */
    public function get(string $url, array $options = []): GuzzleClientResponse
    {
        return $this->request('GET', $url, $options);
    }

    /**
     * Create and send an POST HTTP request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     *
     * @param string $url
     * @param array $options
     *
     * @return GuzzleClientResponse|T
     * @throws GuzzleException
     */
    public function post(string $url, array $options = []): GuzzleClientResponse
    {
        return $this->request('POST', $url, $options);
    }

    /**
     * Create and send an HTTP request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well.
     *
     * @param string $method HTTP method.
     * @param string $url
     * @param array $options Request options to apply. See \GuzzleHttp\RequestOptions.
     *
     * @return GuzzleClientResponse|T
     * @throws GuzzleClientException|GuzzleException
     */
    public function request(string $method, string $url, array $options = []): GuzzleClientResponse
    {
        $start = Carbon::now();

        $options[RequestOptions::HEADERS] = array_merge(
            $options[RequestOptions::HEADERS] ?? [],
            $this->headers
        );

        $response = $this->client->request($method, $url, $options);

        return new $this->responseClassName(
            $method,
            $url,
            $options,
            $response,
            $start
        );
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
}
