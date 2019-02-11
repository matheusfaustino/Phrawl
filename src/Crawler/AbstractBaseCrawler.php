<?php

namespace Phrawl\Crawler;

use Phrawl\Queue\MemoryQueue;
use Phrawl\Queue\QueueInterface;
use Phrawl\Request\Handlers\ArtaxRequestRequestHandler;
use Phrawl\Request\Handlers\PantherRequestRequestHandler;
use Phrawl\Request\Types\RequestInterface;
use Phrawl\YieldValues\YieldDumpDataHandler;
use Phrawl\YieldValues\YieldRequestObjectHandler;
use Phrawl\YieldValues\YieldStringUrlToQueueValueHandler;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\Client;

/**
 * Class AbstractBaseCrawler
 *
 * @package Phrawl\Crawler
 */
abstract class AbstractBaseCrawler implements CrawlerInterface
{
    /**
     * Initial URL
     *
     * @var null|string|array
     */
    protected $startUrls;

    /**
     * Crawler's name
     *
     * @var string
     */
    public $name = 'Crawler';

    /**
     * @param Crawler          $crawler
     *
     * @param RequestInterface $request
     *
     * @param Client|null      $pantherClient
     *
     * @return \Generator|void
     */
    abstract public function parser(Crawler $crawler, RequestInterface $request, ?Client $pantherClient = null);

    /**
     * Initial url to start the crawling
     *
     * @return \Generator
     * @throws NoStartUrlException
     */
    public function getStartUrl(): \Generator
    {
        if ($this->startUrls === null) {
            throw new NoStartUrlException('You need to pass a string or an array as startUrl.');
        }

        $this->startUrls = is_string($this->startUrls) ? [$this->startUrls] : $this->startUrls;

        foreach ($this->startUrls as $url) {
            yield $url;
        }
    }

    /**
     * Default Queue engine
     *
     * @return QueueInterface
     */
    public function getQueueEngine(): QueueInterface
    {
        return new MemoryQueue();
    }

    /**
     * @return array
     */
    public function getYieldHandlers(): array
    {
        return [
            new YieldRequestObjectHandler(),
            new YieldStringUrlToQueueValueHandler(),
            new YieldDumpDataHandler(),
        ];
    }

    /**
     * @return array
     */
    public function getRequestHandlers(): array
    {
        return [
            new ArtaxRequestRequestHandler(),
            new PantherRequestRequestHandler(),
        ];
    }
}
