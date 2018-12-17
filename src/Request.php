<?php

namespace Phrawl;

use Psr\Http\Message\StreamInterface;

/**
 * Class Request
 *
 * @package Phrawl
 */
class Request
{
    /**
     * @var string
     */
    protected $method;
    /**
     * @var string
     */
    protected $url;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var string|null|resource|StreamInterface
     */
    protected $body;

    /**
     * @var string|array|callable
     */
    protected $callback;

    /**
     * Request constructor.
     *
     * @param string $url
     * @param null   $callback
     * @param string $method
     * @param array  $headers
     * @param null   $body
     * @param array  $meta
     */
    public function __construct(
        string $url,
        $callback = null,
        string $method = 'GET',
        array $headers = [],
        $body = null,
        array $meta = []
    ) {
        $this->method = $method;
        $this->url = $url;
        $this->callback = $callback;
        $this->headers = $headers;
        $this->body = $body;
        $this->meta = $meta;
    }

    /**
     * Extra parameters
     *
     * @var array
     */
    protected $meta;

    /**
     * @todo need other HTTP Methods support
     *
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getRequest(): \GuzzleHttp\Psr7\Request
    {
        return new \GuzzleHttp\Psr7\Request($this->method, $this->url, $this->headers, $this->body);
    }

    /**
     * @return array|callable|string
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    public function __toString()
    {
        return \sprintf('%s %s', \strtoupper($this->method), $this->url);
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
