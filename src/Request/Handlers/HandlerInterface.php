<?php

namespace Phrawl\Request\Handlers;

use Amp\Promise;
use Phrawl\Request\Types\RequestInterface;
use React\Promise\PromiseInterface;

/**
 * Interface HandlerInterface
 * Chain of Responsibility Pattern
 *
 * @package Phrawl\Request
 */
interface HandlerInterface
{
    /**
     * Handle request object
     * Return `null` if it is not satisfies the handler or return a promise
     *
     * @param RequestInterface $request
     *
     * @return Promise|PromiseInterface|null
     */
    public function handle(RequestInterface $request);
}
