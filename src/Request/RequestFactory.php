<?php

namespace Phrawl\Request;

use Phrawl\Request\Types\ArtaxRequest;
use Phrawl\Request\Types\PantherRequest;
use Phrawl\Request\Types\RequestInterface;

/**
 * Class RequestFactory
 *
 * @package Phrawl\Request
 */
class RequestFactory
{
    /**
     * @param string $method
     * @param string $uri
     * @param array  $headers
     * @param null   $body
     * @param null   $callable
     * @param array  $meta
     *
     * @return RequestInterface
     */
    public static function new(
        string $method,
        string $uri,
        array $headers = [],
        $body = null,
        $callable = null,
        array $meta = []
    ): RequestInterface {
        return new ArtaxRequest($method, $uri, $headers, $body, $callable, $meta);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array  $headers
     * @param null   $body
     * @param null   $callable
     * @param string $waitFor
     * @param array  $meta
     *
     * @return RequestInterface
     */
    public static function newWebDriver(
        string $method,
        string $uri,
        array $headers = [],
        $body = null,
        $callable = null,
        string $waitFor = '',
        array $meta = []
    ): RequestInterface {
        return new PantherRequest($method, $uri, $headers, $body, $callable, $waitFor, $meta);
    }
}
