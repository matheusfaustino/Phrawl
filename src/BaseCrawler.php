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
    protected $name = 'base_crawler';

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
     * @todo remove it
     * @var array
     */
    protected $copy_start_urls = [];

    /**
     * Returns the urls to the engine
     *
     * @return \Generator
     */
    public function startUrls()
    {
        // (normalize)
        $this->start_urls = array_map(function ($i) {
            return [$i, 'parser'];
        }, $this->start_urls);

        //hard copy
        foreach ($this->start_urls as $arr) {
            $this->copy_start_urls[$arr[0]] = $arr[1];
        }

        // remove from the beginning
        while ($url = array_shift($this->start_urls)) {
            printf("[LOG] Yielding %s\n", $url[0]);

            yield $url[0];
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
     * @param $url
     * @param $callback
     */
    public function addNewUrl($url, $callback)
    {
        printf("[LOG] Added URL %s\n", $url);

        $this->start_urls[] = [$url, $callback];
        $this->copy_start_urls[$url] = $callback;
    }

    /**
     * @param $index
     *
     * @return string
     */
    public function getCallbackRequest($index): string
    {
        return $this->copy_start_urls[$index];
    }

    /**
     * @return array
     */
    public function getConfigs(): array
    {
        return $this->configs;
    }
}
