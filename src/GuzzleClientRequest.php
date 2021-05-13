<?php

namespace Adapterap\GuzzleClient;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

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
     * Create and send an GET HTTP request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     *
     * @param string $url
     * @param array  $options
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
     * Create and send an POST HTTP request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     *
     * @param string $url
     * @param array  $options
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
     * @param string $method
     * @param string $url
     * @param array  $options
     *
     * @throws GuzzleException
     *
     * @return GuzzleClientResponse|T
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
}
