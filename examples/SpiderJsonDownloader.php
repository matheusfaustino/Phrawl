<?php

use Phrawl\BaseCrawler;
use Phrawl\Request;
use Phrawl\Response;

require __DIR__.'/../vendor/autoload.php';

final class SpiderJsonDownloader extends BaseCrawler
{
    public $name = 'json-downloader';
    protected $configs = ['concurrency' => 2];
    public $start_urls = ['https://jsonplaceholder.typicode.com/'];

    /**
     * Default "parser"
     *
     * @param Response $response
     *
     * @return Generator
     */
    public function parser(Response $response)
    {
        $crawler = $response->getCrawler();

        $url = $crawler->filter('.resources tr:first-child a')->first()->link()->getUri();
        yield (new Request($url))
            ->setSaveTo(__DIR__.'/post.json');
    }
}
