<?php

namespace Adapterap\GuzzleClient;

/**
 * @template T of GuzzleClientResponse
 */
abstract class GuzzleClient
{
    /**
     * Формирует и возвращает объект для отправки запросов.
     *
     * @return GuzzleClientRequest<T>
     */
    abstract protected function getRequest(): GuzzleClientRequest;
}
