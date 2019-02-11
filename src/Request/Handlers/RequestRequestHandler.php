<?php

namespace Phrawl\Request\Handlers;

use Amp\Promise;
use Phrawl\Request\Types\RequestInterface;

/**
 * Class RequestRequestHandler
 *
 * @package Phrawl\Request
 */
final class RequestRequestHandler implements RequestHandlerInterface
{
    /**
     * @var RequestHandlerInterface[]
     */
    private $handlers = [];

    /**
     * RequestRequestHandler constructor.
     *
     * @param RequestHandlerInterface[] $handlers
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
     * @throws NoRequestHandlerAvailableException
     * @throws UnhandleRequestException
     */
    public function handle(RequestInterface $request)
    {
        if (count($this->handlers) === 0) {
            throw new NoRequestHandlerAvailableException('No handler added');
        }

        /* @todo throws exception if it's empty */
        foreach ($this->handlers as $handler) {
            $requestReturn = $handler->handle($request);
            if ($requestReturn !== null) {
                return $requestReturn;
            }
        }

        throw new UnhandleRequestException('Unhandled Request');
    }
}
