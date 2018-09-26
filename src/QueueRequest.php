<?php
/**
 * Created by PhpStorm.
 * User: matheusfaustino
 * Date: 5/23/17
 * Time: 12:56
 */

namespace Phpcrawler;


use function GuzzleHttp\Promise\iter_for;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Request;

class QueueRequest
{
    /**
     * @var Promise[]
     */
    private $queue = [];

    /**
     * Queue constructor.
     * @param Promise|Promise[] $queue
     */
    public function __construct(array $queue = [])
    {
        $this->queue = $queue;
    }

    /**
     * @return generator
     */
    public function getQueue()
    {
        while ($promise = array_pop($this->queue)) {
            printf("[INFO] New Request\n");

            yield function() use($promise) {
                return $promise;
            };
        }
    }

    public function countQueue() : int
    {
        return count($this->queue);
    }

    /**
     * @param Promise[] $queue
     */
    public function setQueue(array $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @param Promise $item
     */
    public function addItemQueue(Promise $item)
    {
        printf("[LOG] Added Item \n");
        $this->queue[] = $item;
    }
}
