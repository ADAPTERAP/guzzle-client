<?php

namespace Adapterap\GuzzleClient;

abstract class GuzzleClient
{
    /**
     * Формирует и возвращает объект для отправки запросов.
     *
     * @return GuzzleClientRequest
     */
    abstract protected function getRequest(): GuzzleClientRequest;
}
