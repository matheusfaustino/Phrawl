<?php

namespace Phpcrawler;


use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\DomCrawler\Crawler;

final class ProcessorPoolRequest
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
        foreach ($this->spider->startUrls() as $url) {
            \printf("[LOG] Requesting %s\n", $url);
//            yield new Request('GET', $url);
            yield function () use ($url) {
                return $this->client->getAsync($url)
                    ->then(function (Response $response) use ($url) {
                        // calling user function from here, because
                        // if I use the fullfilledRequest not all of them will be processed
                        \printf("[INFO] Calling Fulfilled #%s\n", $url);

                        $urlObject = $this->spider->getCallbackRequest($url);
                        $currentUrl = \parse_url($url);
                        $crawler = new Crawler(
                            null,
                            $currentUrl['path'],
                            \sprintf('%s://%s', $currentUrl['scheme'], $currentUrl['host'])
                        );
                        $crawler->addContent($response->getBody());

                        $reflection = new \ReflectionMethod($this->spider, $urlObject);

                        if ($reflection->isGenerator()) {
                            foreach ($reflection->invoke($this->spider, $crawler) as $url => $callback) {
                                $this->spider->addNewUrl($url, $callback ?? 'parser');
                            }
                        } else {
                            $reflection->invoke($this->spider, $crawler);
                        }

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
