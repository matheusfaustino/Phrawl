<?php

namespace Phrawl\Request\Types;

/**
 * Interface RequestInterface
 *
 * @package Phrawl\Request\Types
 */
interface RequestInterface
{
    /**
     * @param string $meta
     * @param        $value
     *
     * @return RequestInterface
     */
    public function withMeta(string $meta, $value): RequestInterface;

    /**
     * @param array $metas
     *
     * @return RequestInterface
     */
    public function withMetas(array $metas): RequestInterface;

    /**
     * @param string $method
     *
     * @return mixed
     */
    public function withMethod($method): RequestInterface;

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @param string $header
     * @param string $value
     *
     * @return RequestInterface
     */
    public function withHeader(string $header, string $value): RequestInterface;

    /**
     * @param array $headers
     *
     * @return RequestInterface
     */
    public function withHeaders(array $headers): RequestInterface;

    /**
     * @return array
     */
    public function getHeaders(): array;

    /**
     * @param string $uri
     *
     * @return mixed
     */
    public function withUri($uri): RequestInterface;

    /**
     * @return string
     */
    public function getUri(): string;

    /**
     * @return array|string
     */
    public function getCallable();

    /**
     * @param callable|array $callable
     *
     * @return RequestInterface
     */
    public function withCallback($callable): RequestInterface;

    /**
     * @param $body
     *
     * @return RequestInterface
     */
    public function withBody($body): RequestInterface;
}
