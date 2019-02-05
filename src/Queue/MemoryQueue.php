<?php

namespace Phrawl\Queue;

/**
 * Class MemoryQueue
 *
 * @package Phrawl\Queue
 */
final class MemoryQueue implements QueueInterface
{
    /**
     * @var array
     */
    private $queue;

    /**
     * Add Item to the queue
     *
     * @param string $url
     */
    public function addItem(string $url): void
    {
        $this->queue[] = $url;
    }

    /**
     * Count the list of URLs from the queue
     *
     * @return int
     */
    public function countItems(): int
    {
        return \count($this->queue);
    }

    /**
     * Get the list of the Urls from source
     *
     * @return \Generator
     */
    public function fetch(): \Generator
    {
        while ($url = array_pop($this->queue)) {
            yield $url;
        }
    }
}
