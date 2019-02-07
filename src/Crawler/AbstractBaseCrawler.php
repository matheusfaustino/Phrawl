<?php

namespace Phrawl\Crawler;

use Phrawl\Queue\MemoryQueue;
use Phrawl\Queue\QueueInterface;
use Phrawl\Request\Types\RequestInterface;
use Symfony\Component\DomCrawler\Crawler;

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
     * @return \Generator|void
     */
    abstract public function parser(Crawler $crawler, RequestInterface $request);

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
}
