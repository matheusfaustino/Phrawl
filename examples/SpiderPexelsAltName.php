<?php

use Phrawl\BaseCrawler;
use Phrawl\Response;

require __DIR__.'/../vendor/autoload.php';

final class SpiderPexelsAltName extends BaseCrawler
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

            yield (new \Phrawl\Request($images->getAttribute('src'), 'save'))
                ->setMeta(['name' => $images->getAttribute('alt')]);
        }
    }

    public function save(Response $response)
    {
        file_put_contents(
            sprintf(__DIR__.'/%s.jpeg', $response->getMetaData()['name']),
            $response->getResponse()->getBody()
        );
    }
}
