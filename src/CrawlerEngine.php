<?php

namespace Phrawl;

use function Amp\Promise\all;
use function Amp\Promise\wait;
use Phrawl\Crawler\CrawlerInterface;
use Phrawl\Queue\QueueInterface;
use Phrawl\Request\Handlers\ArtaxHandler;
use Phrawl\Request\Handlers\Handler;
use Phrawl\Request\Handlers\HandlerInterface;
use Phrawl\Request\Handlers\PantherHandler;
use Phrawl\Request\RequestFactory;
use Phrawl\Request\Types\RequestInterface;
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
     * @var HandlerInterface
     */
    private $handler;

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
     * @throws Crawler\NoStartUrlException
     */
    private function setUp(): void
    {
        $this->queue = $this->queue ?? $this->crawler->getQueueEngine();
        /* @todo Add handlers here (Panther or Artax) */
        $this->handler = new Handler([
            new PantherHandler(),
            new ArtaxHandler(),
        ]);

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
            /* @todo implement Strategy patter for request */
            $requestHandled = $this->handler->handle($url);
            $requests[] = $requestHandled;

            $requestHandled->onResolve(function (?\Throwable $reason, ...$arguments) use ($url) {
                if ($reason !== null) {
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
                        $callback ?? 'parser'
                    );
                    $invokedFunc = $reflection->invokeArgs($this->crawler, array_filter([$crawler, $request, $client]));
                }

                if ($reflection->isGenerator()) {
                    foreach ($invokedFunc as $yieldedValue) {
                        if ($yieldedValue instanceof RequestInterface) {
                            $this->queue->addItem($yieldedValue);
                        } elseif (is_string($yieldedValue)) {
                            $this->queue->addItem(RequestFactory::new('GET', $yieldedValue));
                        }

                        continue;
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
     * @param HandlerInterface $handler
     *
     * @return CrawlerEngine
     */
    public function setHandler(HandlerInterface $handler): CrawlerEngine
    {
        $this->handler = $handler;

        return $this;
    }
}
