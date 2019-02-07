<?php

namespace Phrawl\Request\Handlers;

use Amp\Promise;
use Phrawl\Request\Types\RequestInterface;

/**
 * Class Handler
 *
 * @package Phrawl\Request
 */
final class Handler implements HandlerInterface
{
    /**
     * @var HandlerInterface[]
     */
    private $handlers = [];

    /**
     * Handler constructor.
     *
     * @param HandlerInterface[] $handlers
     */
    public function __construct(array $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * Handle request object
     *
     * @param RequestInterface $request
     *
     * @return Promise|PromiseInterface|null
     * @throws NoHandlerAvailableException
     * @throws UnhandleException
     */
    public function handle(RequestInterface $request)
    {
        if (count($this->handlers) === 0) {
            throw new NoHandlerAvailableException('No handler added');
        }

        /* @todo throws exception if it's empty */
        foreach ($this->handlers as $handler) {
            $requestReturn = $handler->handle($request);
            if ($requestReturn !== null) {
                return $requestReturn;
            }
        }

        throw new UnhandleException('Unhandled Request');
    }
}
