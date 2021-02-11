<?php

namespace Phrawl\Request\Types;

/**
 * Class AbstractRequest
 *
 * @package Phrawl\Request\Types
 */
abstract class AbstractRequest implements RequestInterface
{
    /**
     * @var array
     */
    protected $meta;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var callable|array
     */
    protected $callable;

    /**
     * @var string|array
     */
    protected $body;

    /**
     * AbstractRequest constructor.
     *
     * @param string $method
     * @param string $uri
     * @param array  $headers
     * @param array  $meta
     * @param        $body
     * @param        $callable
     */
    public function __construct(
        string $method,
        string $uri,
        array $headers = [],
        $body = null,
        $callable = null,
        array $meta = []
    ) {
        $this->meta = $meta;
        $this->headers = $headers;
        $this->uri = $uri;
        $this->method = $method;
        $this->callable = $callable;
        $this->body = $body;
    }


    /**
     * @param string $meta
     * @param        $value
     *
     * @return RequestInterface
     */
    public function withMeta(string $meta, $value): RequestInterface
    {
        $this->meta[$meta] = $value;

        return $this;
    }

    /**
     * @param array $metas
     *
     * @return RequestInterface
     */
    public function withMetas(array $metas): RequestInterface
    {
        $this->meta = array_merge($this->meta, $metas);

        return $this;
    }

    /**
     * @param string $method
     *
     * @return mixed
     */
    public function withMethod($method): RequestInterface
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $header
     * @param string $value
     *
     * @return RequestInterface
     */
    public function withHeader(string $header, string $value): RequestInterface
    {
        $this->headers[$header] = $value;

        return $this;
    }

    /**
     * @param array $headers
     *
     * @return RequestInterface
     */
    public function withHeaders(array $headers): RequestInterface
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param string $uri
     *
     * @return mixed
     */
    public function withUri($uri): RequestInterface
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @param array|callable $callable
     *
     * @return RequestInterface
     */
    public function withCallback($callable): RequestInterface
    {
        $this->callable = $callable;

        return $this;
    }

    /**
     * @param $body
     *
     * @return RequestInterface
     */
    public function withBody($body): RequestInterface
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return array|string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return array|callable
     */
    public function getCallable()
    {
        return $this->callable;
    }
}
