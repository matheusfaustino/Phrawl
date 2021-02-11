<?php

namespace Phrawl\YieldValues;

use Phrawl\Request\Types\RequestInterface;
use Phrawl\Traits\UseQueueTrait;

/**
 * Class YieldRequestObject
 *
 * @package Phrawl\YieldValues
 */
final class YieldRequestObjectHandler implements YieldHandlerInterface
{
    use UseQueueTrait;

    /**
     * @param $yieldValue
     *
     * @return mixed
     */
    public function handle($yieldValue): bool
    {
        if ($yieldValue instanceof RequestInterface) {
            $this->queue->addItem($yieldValue);

            return true;
        }

        return false;
    }
}
