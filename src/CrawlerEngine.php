<?php

namespace Phrawl;

use function Amp\Promise\all;
use function Amp\Promise\wait;
use Phrawl\Crawler\CrawlerInterface;
use Phrawl\Queue\QueueInterface;
use Phrawl\Request\Handlers\RequestRequestHandler;
use Phrawl\Request\Handlers\RequestHandlerInterface;
use Phrawl\Request\RequestFactory;
use Phrawl\Request\Types\RequestInterface;
use Phrawl\Traits\UseQueueTrait;
use Phrawl\YieldValues\YieldHandler;
use Phrawl\YieldValues\YieldHandlerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\Client;

/**
 * Class CrawlerEngine
 *
 * @package Phrawl
 */
final class CrawlerEngine
{
    /**
     * @var CrawlerInterface
     */
    private $crawler;

    /**
     * @var QueueInterface
     */
    private $queue;

    /**
     * @var RequestHandlerInterface
     */
    private $requestHandler;

    /**
     * @var RequestHandlerInterface[]
     */
    private $requestHandlers;

    /**
     * @var YieldHandlerInterface
     */
    private $yieldHandler;

    /**
     * @var null|YieldHandlerInterface[]
     */
    private $yieldHandlers;

    /**
     * CrawlerEngine constructor.
     *
     * @param CrawlerInterface $crawler
     */
    public function __construct(CrawlerInterface $crawler)
    {
        $this->crawler = $crawler;
    }

    /**
     * Setting up engine.
     * I do not know if this is the "right way", but let's keep this way, for now
     *
     * @throws Crawler\NoStartUrlException
     */
    private function setUp(): void
    {
        $this->queue = $this->queue ?? $this->crawler->getQueueEngine();
        /* @todo Add handlers here (Panther or Artax) */
        $this->requestHandler = $this->requestHandler ?? new RequestRequestHandler(
                $this->requestHandlers ?? $this->crawler->getRequestHandlers()
            );

        $yieldsObj = $this->yieldHandlers ?? $this->crawler->getYieldHandlers();
        foreach ($yieldsObj as $yield) {
            if (in_array(UseQueueTrait::class, class_uses($yield), false)) {
                $yield->setQueue($this->queue);
            }
        }
        $this->yieldHandler = $this->yieldHandler ?? new YieldHandler($yieldsObj);

        foreach ($this->crawler->getStartUrl() as $url) {
            if (is_string($url)) {
                $url = RequestFactory::new('GET', $url);
            }

            $this->queue->addItem($url);
        }
    }

    /**
     * @throws \Throwable
     */
    public function run(): void
    {
        $this->setUp();

        $requests = [];
        foreach ($this->queue->fetch() as $url) {
            /* @todo implement plugin handler */
            $requestHandled = $this->requestHandler->handle($url);
            $requests[] = $requestHandled;

            $requestHandled->onResolve(function (?\Throwable $reason, ...$arguments) use ($url) {
                if ($reason !== null) {
                    var_dump($reason);
                    exit(1);
                }

                /* @var $crawler Crawler */
                /* @var $request RequestInterface */
                /* @var $client Client */
                $client = null;
                $arguments = $arguments[0];
                count($arguments) === 2 and [$crawler, $request] = $arguments;
                count($arguments) === 3 and [$crawler, $request, $client] = $arguments;

                $callback = $request->getCallable();
                if ($callback instanceof \Closure) {
                    $reflection = new \ReflectionFunction($callback);
                    $invokedFunc = $reflection->invokeArgs(array_filter([$crawler, $request, $client]));
                } else {
                    $reflection = new \ReflectionMethod(
                        \is_array($callback) ? $callback[0] : $this->crawler,
                        \is_array($callback) ? $callback[1] : 'parser'
                    );
                    $invokedFunc = $reflection->invokeArgs($this->crawler, array_filter([$crawler, $request, $client]));
                }

                if ($reflection->isGenerator()) {
                    foreach ($invokedFunc as $yieldedValue) {
                        $this->yieldHandler->handle($yieldedValue);
                    }

                    return;
                }
            });

            /* @todo implement log system */
            if ($this->queue->countItems() === 0 || \count($requests) >= 10) {
                wait(all($requests));
                $requests = [];
            }
        }
    }

    /**
     * @param QueueInterface $queue
     *
     * @return CrawlerEngine
     */
    public function setQueue(QueueInterface $queue): CrawlerEngine
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * @param YieldHandlerInterface $yieldHandler
     *
     * @return CrawlerEngine
     */
    public function setYieldHandler(YieldHandlerInterface $yieldHandler): CrawlerEngine
    {
        $this->yieldHandler = $yieldHandler;

        return $this;
    }

    /**
     * @param YieldHandlerInterface[]|null $yieldHandlers
     *
     * @return CrawlerEngine
     */
    public function setYieldHandlers(?array $yieldHandlers): CrawlerEngine
    {
        $this->yieldHandlers = $yieldHandlers;

        return $this;
    }

    /**
     * @param RequestHandlerInterface $requestHandler
     *
     * @return CrawlerEngine
     */
    public function setRequestHandler(RequestHandlerInterface $requestHandler): CrawlerEngine
    {
        $this->requestHandler = $requestHandler;

        return $this;
    }

    /**
     * @param RequestHandlerInterface[] $requestHandlers
     *
     * @return CrawlerEngine
     */
    public function setRequestHandlers(array $requestHandlers): CrawlerEngine
    {
        $this->requestHandlers = $requestHandlers;

        return $this;
    }
}
