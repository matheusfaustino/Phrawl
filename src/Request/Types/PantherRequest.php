<?php

namespace Phrawl\Request\Types;

/**
 * Class PantherRequest
 *
 * @package Phrawl\Request\Types
 */
final class PantherRequest extends AbstractRequest
{
    /**
     * @var string Select element to wait the browser render
     */
    private $waitFor;

    /**
     * AbstractRequest constructor.
     *
     * @param string $method
     * @param string $uri
     * @param array  $headers
     * @param        $body
     * @param        $callable
     * @param string $waitFor
     * @param array  $meta
     */
    public function __construct(
        string $method,
        string $uri,
        array $headers = [],
        $body = null,
        $callable = null,
        string $waitFor = '',
        array $meta = []
    ) {
        $this->meta = $meta;
        $this->headers = $headers;
        $this->uri = $uri;
        $this->method = $method;
        $this->callable = $callable;
        $this->waitFor = $waitFor;
        $this->body = $body;
    }


    /**
     * @param string $waitFor
     *
     * @return RequestInterface
     */
    public function withWaitFor(string $waitFor): RequestInterface
    {
        $this->waitFor = $waitFor;

        return $this;
    }

    /**
     * @return string|null string
     */
    public function getWaitFor(): ?string
    {
        return $this->waitFor;
    }
}
