<?php

namespace Phpcrawler;


use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\DomCrawler\Crawler;

class ProcessorPoolRequest
{
    /**
     * @var BaseCrawler
     */
    private $spider;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var array
     */
    private $configs
        = [
            'concurrency' => 5,
        ];

    /**
     * Processor constructor.
     *
     * @param BaseCrawler $spider
     */
    public function __construct(BaseCrawler $spider)
    {
        $this->spider = $spider;
    }

    /**
     * Setting up request pool
     */
    private function settingUp()
    {
        $this->configs = array_merge($this->configs, $this->spider->getConfigs());

        $this->client = new Client();

        $this->pool = new Pool($this->client, $this->requestsPool(), [
            'concurrency' => $this->dynamicConcurrencyNumber(),
            'fulfilled' => $this->fullfilledRequest(),
            'rejected' => $this->rejectedRequest(),
        ]);
    }

    /**
     * For now, it's dummy
     *
     * @return \Closure
     */
    private function fullfilledRequest(): callable
    {
        return function (Response $response, $i) {
            // dummy function
        };
    }

    /**
     * A little hack to be able to add more urls to the pool using Guzzle
     *
     * @return \Closure
     */
    private function dynamicConcurrencyNumber(): callable
    {
        return function () {
            return \max(
                1,
                \min(\count($this->spider->getUrls()), $this->configs['concurrency'])
            );
        };
    }

    private function rejectedRequest(): callable
    {
        return function ($reason) {
            \printf("[ERROR] %s\n", $reason);
        };
    }

    private function requestsPool()
    {
        foreach ($this->spider->startUrls() as $request) {
            \printf("[LOG] Requesting %s\n", $request->getUrl());

            yield function () use ($request) {
                return $this->client->sendAsync($request->getRequest())
                    ->then(function (Response $response) use ($request) {
                        // calling user function from here, because
                        // if I use the fullfilledRequest not all of them will be processed
                        \printf("[INFO] Calling Fulfilled #%s\n", $request);

                        $currentUrl = \parse_url($request->getUrl());
                        $crawler = new Crawler(
                            null,
                            $currentUrl['path'],
                            \sprintf('%s://%s', $currentUrl['scheme'], $currentUrl['host'])
                        );
                        $crawler->addContent($response->getBody());

                        $callback = $request->getCallback();
                        if ($callback instanceof \Closure) {
                            $reflection = new \ReflectionFunction($callback);
                        } else {
                            $reflection = new \ReflectionMethod(
                                \is_array($callback) ? $callback[0] : $this->spider,
                                $callback ?? 'parser'
                            );
                        }

                        if ($reflection->isGenerator()) {
                            foreach ($reflection->invoke($this->spider, $crawler) as $yieldedValue) {
                                if ($yieldedValue instanceof \Phpcrawler\Request) {
                                    $this->spider->addNewUrl($yieldedValue);

                                    continue;
                                }
                                // @todo implement more ways
                            }

                            return $response;
                        }

                        $reflection->invoke($this->spider, $crawler);

                        return $response;
                    });
            };
        }
    }

    /**
     * that is it
     */
    public function run()
    {
        $this->settingUp();
        $this->pool
            ->promise()
            ->wait();
    }
}
