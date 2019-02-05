<?php

namespace Phrawl\Queue;

/**
 * Interface QueueInterface
 *
 * @package Phrawl\Queue
 */
interface QueueInterface
{
    /**
     * Add Item to the queue
     *
     * @param string $url
     */
    public function addItem(string $url): void;

    /**
     * Count the list of URLs from the queue
     *
     * @return int
     */
    public function countItems(): int;

    /**
     * Get the list of the Urls from source
     *
     * @return \Generator
     */
    public function fetch(): \Generator;
}
