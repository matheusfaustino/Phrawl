<?php

namespace Phrawl\Crawler;

use Phrawl\Queue\QueueInterface;
use Phrawl\Request\Types\RequestInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\Client;

/**
 * Interface CrawlerInterface
 *
 * @package Phrawl\Crawler
 */
interface CrawlerInterface
{
    /**
     * Initial url to start the crawling
     *
     * @return \Generator
     * @throws NoStartUrlException
     */
    public function getStartUrl(): \Generator;

    /**
     * Responsible for parsing the request's response, default function
     *
     * @param Crawler          $crawler
     * @param RequestInterface $request
     * @param Client|null      $pantherClient
     *
     * @return void|\Generator
     */
    public function parser(Crawler $crawler, RequestInterface $request, ?Client $pantherClient = null);

    /**
     * Return queue object to the engine. Crawler is responsible to easier to replace
     *
     * @return QueueInterface
     */
    public function getQueueEngine(): QueueInterface;
}
