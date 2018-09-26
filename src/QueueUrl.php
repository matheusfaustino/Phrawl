<?php

namespace Phpcrawler;

use GuzzleHttp\Psr7\Request;

/**
 * Class QueueUrl
 * @package Phpcrawler
 *
 * Classe simples, só para ver se é possível adicionar as urls dentro de um laço de repetição
 */
class QueueUrl
{
    /**
     * @var string[]
     */
    private $queue = [];

    /**
     * Queue constructor.
     * @param array $queue
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
        while ($url = array_pop($this->queue)) {
            printf("[LOG] New Request: %s\n", $url);

            yield new Request('GET' , $url);
        }
    }

    public function countQueue() : int
    {
        return count($this->queue);
    }

    /**
     * @param array $queue
     */
    public function setQueue(array $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @param string $item
     */
    public function addItemQueue(string $item)
    {
        printf("[LOG] Added Item : %s\n", $item);
        $this->queue[] = $item;
    }
}
