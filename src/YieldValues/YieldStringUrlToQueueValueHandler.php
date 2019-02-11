<?php

namespace Phrawl\YieldValues;

use Phrawl\Request\RequestFactory;
use Phrawl\Traits\UseQueueTrait;

/**
 * Class YieldStringToQueueValue
 *
 * @package Phrawl\YieldValues
 */
final class YieldStringUrlToQueueValueHandler implements YieldHandlerInterface
{
    use UseQueueTrait;

    /**
     * @param $yieldValue
     *
     * @return mixed
     */
    public function handle($yieldValue): bool
    {
        if (is_string($yieldValue) === false || filter_var($yieldValue, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $this->queue->addItem(RequestFactory::new('GET', $yieldValue));

        return true;
    }
}
