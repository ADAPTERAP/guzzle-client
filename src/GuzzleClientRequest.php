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
abstract class GuzzleClientRequest
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
     * GuzzleClientRequest constructor.
     */
    public function __construct()
    {
        $this->client = $this->getClient();
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
     *
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
     * @return GuzzleClientResponse
     * @throws GuzzleClientException
     */
    public function request(string $method, string $url, array $options = []): GuzzleClientResponse
    {
        $start = Carbon::now();
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
     * Возвращает клиент для работы с HTTP.
     *
     * @return Client
     */
    protected function getClient(): Client
    {
        return new Client([
            RequestOptions::VERIFY => false,
            RequestOptions::HTTP_ERRORS => false,
        ]);
    }
}
