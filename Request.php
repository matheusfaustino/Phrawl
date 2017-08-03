<?php
namespace Phpcrawler;


class Request
{
    /**
     * @var string
     */
    protected $method = 'GET';

    /**
     * @var string
     */
    protected $url;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var string
     */
    protected $body = null;

    /**
     * @var null
     */
    protected $callback;

    /**
     * Request constructor.
     * @param string $method
     * @param string $url
     * @param callable $callback
     */
    public function __construct($method, $url, callable $callback)
    {
        $this->method = $method;
        $this->url = $url;
        $this->callback = $callback;
    }

    public function getRequest(): \GuzzleHttp\Psr7\Request
    {
        return new \GuzzleHttp\Psr7\Request($this->method, $this->url, $this->headers, $this->body);
    }
}
