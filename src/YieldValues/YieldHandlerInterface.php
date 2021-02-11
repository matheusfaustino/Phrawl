<?php

namespace Phrawl\YieldValues;

/**
 * Interface YieldHandlerInterface
 *
 * @package Phrawl\YieldValues
 */
interface YieldHandlerInterface
{
    /**
     * @param $yieldValue
     *
     * @return bool
     */
    public function handle($yieldValue): bool;
}
