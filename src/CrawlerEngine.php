<?php

namespace Phrawl;

use function Amp\call;
use function Amp\Promise\all;
use function Amp\Promise\wait;
use Phrawl\Crawler\CrawlerInterface;
use Phrawl\Queue\QueueInterface;

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

        foreach ($this->crawler->getStartUrl() as $url) {
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
            /* @todo implement chain of responsibility for callback */
            /* @todo implement log system */
            if ($this->queue->countItems() === 0 || \count($requests) >= 10) {
                wait(all($requests));
                $requests = [];
            }
        }
    }

    /**
     * @param QueueInterface $queue
     */
    public function setQueue(QueueInterface $queue): void
    {
        $this->queue = $queue;
    }
}
