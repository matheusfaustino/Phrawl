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
     * Extra parameters
     *
     * @var array
     */
    protected $meta;

    /**
     * @var string
     */
    private $saveTo;

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

    /**
     * @param string $method
     *
     * @return Request
     */
    public function setMethod(string $method): Request
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @param array $headers
     *
     * @return Request
     */
    public function setHeaders(array $headers): Request
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param null|StreamInterface|resource|string $body
     *
     * @return Request
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @param array|callable|string $callback
     *
     * @return Request
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * @param array $meta
     *
     * @return Request
     */
    public function setMeta(array $meta): Request
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * @param string|resource|StreamInterface $saveTo
     *
     * @return Request
     */
    public function setSaveTo($saveTo): Request
    {
        $this->saveTo = $saveTo;

        return $this;
    }

    /**
     * @see http://docs.guzzlephp.org/en/stable/request-options.html#sink
     * @return array
     */
    public function getOptions(): array
    {
        $arr = [];
        if ($this->saveTo) {
            $arr['sink'] = $this->saveTo;
        }

        return $arr;
    }
}
