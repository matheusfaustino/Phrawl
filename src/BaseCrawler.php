<?php

namespace Phpcrawler;

use Phpcrawler\Interfaces\InterfaceCrawler;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class BaseCrawler
 *
 * @package Phpcrawler
 */
abstract class BaseCrawler implements InterfaceCrawler
{
    /**
     * @var string
     */
    public $name = 'default_name';

    /**
     * For now, it is just one
     *
     * @var array
     */
    protected $configs = ['concurrency' => 5];

    /**
     * List of Urls or you can change the startUrls function
     *
     * @var array
     */
    protected $start_urls = [];

    /**
     * Returns the urls to the engine
     *
     * @todo define possible yielded type
     *
     * @return \Generator|Request[]
     */
    public function startUrls()
    {
        // normalize value
        $this->start_urls = array_map(function ($url) {
            return new Request($url);
        }, $this->start_urls);

        // remove from the beginning
        while ($request = array_shift($this->start_urls)) {
            yield $request;
        }
    }

    /**
     * Default "parser"
     *
     * @param Crawler $response
     */
    abstract public function parser(Crawler $response);

    /**
     * @return array
     */
    public function getUrls(): array
    {
        return $this->start_urls;
    }

    /**
     * Add new Request to start urls
     *
     * @param Request $request
     */
    public function addNewUrl(Request $request)
    {
        $this->start_urls[] = $request;
    }

    /**
     * @return array
     */
    public function getConfigs(): array
    {
        return $this->configs;
    }
}
