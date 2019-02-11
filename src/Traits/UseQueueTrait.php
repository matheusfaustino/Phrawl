<?php

namespace Phrawl\Traits;

use Phrawl\Queue\QueueInterface;

trait UseQueueTrait
{
    /**
     * @var QueueInterface
     */
    private $queue;

    /**
     * @param QueueInterface $queue
     */
    public function setQueue(QueueInterface $queue): void
    {
        $this->queue = $queue;
    }
}
