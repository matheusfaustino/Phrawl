<?php

namespace Phrawl\YieldValues;

/**
 * Class YieldDumpData
 *
 * @package Phrawl\YieldValues
 */
final class YieldDumpDataHandler implements YieldHandlerInterface
{
    /**
     * @param $yieldValue
     *
     * @return mixed
     */
    public function handle($yieldValue): bool
    {
        dump($yieldValue);

        return true;
    }
}
