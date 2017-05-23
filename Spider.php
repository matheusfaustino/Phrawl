<?php

namespace Phpcrawler;


use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;

class Spider
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var object
     */
    private $queue;

    /**
     * @var array
     */
    private $local_queue = [];

    /**
     * @var array
     */
    private $configs = [
        'concurrency' => 5
    ];

    /**
     * @var int
     */
    private $safe_sleep;

    /**
     * @var callable
     */
    private $fullfilledRequestFunction;

    /**
     * Spider constructor.
     * @param Client $client
     * @param QueueRequest $queue
     * @param array $configs
     */
    public function __construct(Client $client = null, QueueRequest $queue = null, array $configs = [])
    {
        $this->client = $client ?? new Client();

        $this->queue = $queue ?? new QueueRequest();

        $this->configs = array_merge($this->configs, $configs);

        $this->settingUp();
    }

    private function settingUp()
    {
        $this->pool = new Pool($this->client, $this->requestsPool(), [
            'concurrency' => $this->dynamicConcurrencyNumber(),
            'fulfilled' => $this->fullfilledRequest(),
            'rejected' => $this->rejectedRequest()
        ]);

        $this->safe_sleep = function() {
            return (rand() + 1) % 2;
        };
    }

    public function run()
    {
        $this->pool->promise()->wait();
    }

    private function requestsPool()
    {
        $sleep = false;
        foreach($this->queue->getQueue() as $req) {
            yield $req;

            if (!$sleep) {
                $sleep = true;
            }

//            $sleep and sleep(($this->safe_sleep)()) and $sleep = false;
        }
    }

    public function setStartUrl(string $url)
    {
        $r = $this->client->getAsync($url);
        $this->queue->addItemQueue($r);
        $this->local_queue[] = ['request' => $r, 'callback' => null];
    }

    private function dynamicConcurrencyNumber() : int
    {
        return max(1, min($this->queue->countQueue(), $this->configs['concurrency']));
    }

    private function fullfilledRequest()
    {
        return function ($response, $i) {
            printf("[INFO] Calling Fulfilled #%d\n", $i);

            $callback = $this->local_queue[$i]['callback'] ?? $this->fullfilledRequestFunction;

//            $returned = $callback($response);
            foreach($callback($response) as $yield_value) {
                if (is_string($yield_value)) {
                    print $yield_value;
                }

                if (is_array($yield_value)) {
                    $this->queue->addItemQueue($yield_value['request']);
                    $this->local_queue[] = $yield_value;
                }
            }

//            if ($returned instanceof PromiseInterface) {
//                $this->queue->addItemQueue($returned);
//                $this->local_queue[] = ['request' => $returned, 'callback' => null];
//
//                return;
//            }

//            if ($returned)
//                foreach ($returned as $item) {
//                    $this->local_queue[] = $item;
//                    $this->queue->addItemQueue($item['request']);
//                }
        };
    }

    private function rejectedRequest()
    {
        return function ($reason) {
            printf("[ERROR] %s\n", $reason);
        };
    }

    /**
     * @param callable $fullfilledRequestFunction
     */
    public function setFullfilledRequestFunction(callable $fullfilledRequestFunction)
    {
        $this->fullfilledRequestFunction = $fullfilledRequestFunction;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }
}
