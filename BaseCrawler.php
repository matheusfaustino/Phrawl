<?php

namespace Phpcrawler;

use Symfony\Component\DomCrawler\Crawler;

abstract class BaseCrawler implements InterfaceCrawler, \Serializable
{
    protected $name = 'base_crawler';

    protected $configs = ['concurrency' => 5];

    protected $start_urls = [];

    protected $copy_start_urls = [];

    public function startUrls()
    {
        // (normalize)
        $this->start_urls = array_map(function($i) { return [$i, 'parser']; }, $this->start_urls);

        //hard copy
        foreach ($this->start_urls as $arr)
            $this->copy_start_urls[$arr[0]] = $arr[1];

        // remove from the beginning
        while($url = array_shift($this->start_urls)) {
            printf("[LOG] Yielding %s\n", $url[0]);

            yield $url[0];
        }
    }

    public function parser(Crawler $response) {
        var_dump("========== Default Parser");
        var_dump($response->getBaseHref());
        var_dump($response->filterXPath('//title')->text());
        var_dump("==========");
    }

    /**
     * @return array
     */
    public function getUrls(): array
    {
        return $this->start_urls;
    }

    /**
     * Add new Request to start urls
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

    public function serialize(): string
    {
        return \serialize([
            $this->name,
            $this->configs,
            $this->start_urls,
            $this->copy_start_urls,
        ]);
    }

    public function unserialize($serialized)
    {
        var_dump($serialized);
        list(
            $this->name,
            $this->configs,
            $this->start_urls,
            $this->copy_start_urls
        ) = \unserialize($serialized);
    }
}
