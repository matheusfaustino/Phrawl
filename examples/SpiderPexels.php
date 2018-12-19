<?php

use Phrawl\BaseCrawler;
use Phrawl\Response;

require __DIR__.'/../vendor/autoload.php';

final class SpiderPexels extends BaseCrawler
{
    public $name = 'pexels';
    protected $configs = ['concurrency' => 5];
    public $start_urls = ['https://www.pexels.com/'];

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
        $photos = 0;
        foreach ($crawler->filter('.photo-item__img') as $images) {
            if ($photos++ > 10) {
                break;
            }

            yield new \Phrawl\Request($images->getAttribute('src'), 'save');
        }
    }

    public function save(Response $response)
    {
        file_put_contents(__DIR__.'/'.uniqid('pexels_', true).'.jpeg', $response->getResponse()->getBody());
    }
}
