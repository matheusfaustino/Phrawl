<?php

namespace Phrawl\YieldValues;

class YieldHandler implements YieldHandlerInterface
{
    /**
     * @var YieldHandlerInterface[]
     */
    private $handlers;

    /**
     * YieldHandler constructor.
     *
     * @param array $handlers
     */
    public function __construct(array $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * @param YieldHandlerInterface $handler
     */
    public function addHandler(YieldHandlerInterface $handler): void
    {
        $this->handlers[] = $handler;
    }

    /**
     * @param $yieldValue
     *
     * @return mixed
     * @throws UnhandleYieldException
     * @throws NoHandlerYieldValueAvailable
     */
    public function handle($yieldValue): void
    {
        if (count($this->handlers) === 0) {
            throw new NoHandlerYieldValueAvailable('No handler for yield value added');
        }

        foreach ($this->handlers as $handler) {
            $handler->handle($yieldValue);
        }

        throw new UnhandleYieldException('Unhandled yield value');
    }
}
