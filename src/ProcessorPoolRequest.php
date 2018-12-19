<?php

namespace Phrawl;


use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Phrawl\Handlers\RetryRequest;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class ProcessorPoolRequest
 *
 * @package Phrawl
 */
class ProcessorPoolRequest implements LoggerAwareInterface
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $defaultLoggerLevel = Logger::WARNING;

    /**
     * @todo improve this
     * @var int
     */
    private $retryDelay = 5;

    /**
     * @todo improve it
     * @var int
     */
    private $defaultDelay = 0;

    /**
     * @var int
     */
    private $defaulTimeout = 10;

    /**
     * @var array
     */
    private $history = [];

    private $generalStats
        = [
            'download_content_length' => 0,
            'starttransfer_time' => 0,
            'size_download' => 0,
            'redirects_count' => 0,
            'total_time' => 0,
        ];

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
     * @param BaseCrawler     $spider
     * @param LoggerInterface $logger
     */
    public function __construct(
        BaseCrawler $spider,
        LoggerInterface $logger = null
    ) {
        $this->spider = $spider;
        $this->logger = $logger ?? new Logger($this->spider->name);
        $this->client = new Client([
            'handler' => $this->setupStackClient(),
            RequestOptions::DELAY => $this->defaultDelay * 1000,
            RequestOptions::TIMEOUT => $this->defaulTimeout,
            RequestOptions::ON_STATS => function (TransferStats $stats) {
                $this->generalStats['download_content_length'] += $stats->getHandlerStat('download_content_length');
                $this->generalStats['starttransfer_time'] += $stats->getHandlerStat('starttransfer_time');
                $this->generalStats['size_download'] += $stats->getHandlerStat('size_download');
                $this->generalStats['redirects_count'] += $stats->getHandlerStat('redirect_count');
                $this->generalStats['total_time'] += $stats->getHandlerStat('total_time');
            },
        ]);
    }

    /**
     * @todo improve this
     * @return HandlerStack
     */
    private function setupStackClient(): HandlerStack
    {
        $stack = HandlerStack::create(new CurlHandler());
        $stack->push(
            Middleware::retry((new RetryRequest($this->logger))->retry()),
            $this->retryDelay
        );
        $stack->push(Middleware::history($this->history));

        return $stack;
    }

    /**
     * Setting up request pool
     *
     * @throws \Exception
     */
    private function settingUp()
    {
        $this->logger->pushHandler(new StreamHandler(
            $this->spider->name.'.log',
            $this->defaultLoggerLevel
        ));
        $this->logger->pushHandler(new ErrorLogHandler(null, $this->defaultLoggerLevel));
        $this->logger->info('Setting up');

        $this->configs = \array_merge($this->configs, $this->spider->getConfigs());
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

    /**
     * @return callable
     */
    private function rejectedRequest(): callable
    {
        return function ($reason) {
            $this->logger->err($reason);
        };
    }

    /**
     * @return \Generator
     */
    private function requestsPool()
    {
        foreach ($this->spider->startUrls() as $request) {
            $this->logger->info(\sprintf("Requesting %s\n", $request->getUrl()));

            yield function () use ($request) {
                return $this->client->sendAsync($request->getRequest(), $request->getOptions())
                    ->then(function (Response $response) use ($request) {
                        // if saveTo was used, no need for the "then" function
                        // for now, if option has an item, then it is the saveTo param
                        if (count($request->getOptions()) === 1) {
                            return $response;
                        }

                        // calling user function from here, because
                        // if I use the fullfilledRequest not all of them will be processed
                        $this->logger->info(\sprintf("Calling Fulfilled #%s\n", $request));

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

                        $responseBag = new \Phrawl\Response($crawler, $response, $request);
                        if ($reflection->isGenerator()) {
                            foreach ($reflection->invoke($this->spider, $responseBag) as $yieldedValue) {
                                if ($yieldedValue instanceof \Phrawl\Request) {
                                    $this->logger->info(\sprintf("Adding new URL %s\n", $yieldedValue->getUrl()));
                                    $this->spider->addNewUrl($yieldedValue);

                                    continue;
                                }
                                // @todo implement more ways
                            }

                            return $response;
                        }

                        $reflection->invoke($this->spider, $responseBag);

                        return $response;
                    });
            };
        }
    }

    /**
     * that is it
     *
     * @throws \Exception
     */
    public function run()
    {
        $this->settingUp();
        $this->pool
            ->promise()
            ->wait();

        $this->logger->info(\json_encode($this->generalStats + ['total_request' => \count($this->history)]));
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return ProcessorPoolRequest
     */
    public function setLogger(LoggerInterface $logger): ProcessorPoolRequest
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param int $defaultLoggerLevel
     *
     * @return ProcessorPoolRequest
     */
    public function setDefaultLoggerLevel(int $defaultLoggerLevel): ProcessorPoolRequest
    {
        $this->defaultLoggerLevel = $defaultLoggerLevel;

        return $this;
    }
}
