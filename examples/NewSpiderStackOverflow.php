<?php

use Phrawl\Crawler\AbstractBaseCrawler;
use Phrawl\Request\RequestFactory;
use Phrawl\Request\Types\RequestInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\Client;

require __DIR__.'/../vendor/autoload.php';

final class NewSpiderStackOverflow extends AbstractBaseCrawler
{
    public $name = 'stackoverflow';

    protected $startUrls = 'https://stackoverflow.com/questions/23050430/does-selenium-wait-for-javascript-to-complete';

    public function parser(Crawler $crawler, RequestInterface $request, ?Client $pantherClient = null)
    {
        yield RequestFactory::new('GET',
            'https://stackoverflow.com/questions/9291898/selenium-wait-for-javascript-function-to-execute-before-continuing',
            [], null,
            [$this, 'dynamic']);
        yield RequestFactory::new('GET',
            'https://stackoverflow.com/questions/5355121/passing-dict-to-constructor/5355152#5355152', [], null,
            [$this, 'dynamic']);
//        yield RequestFactory::newWebDriver('GET',
//            'https://stackoverflow.com/questions/835501/how-do-you-stash-an-untracked-file', [], null,
//            [$this, 'dynamic']);
    }

    public function dynamic(Crawler $crawler, RequestInterface $request, ?Client $pantherClient = null)
    {
        var_dump('dynamic', $crawler->filterXPath('//title')->text());
    }
}

(new \Phrawl\CrawlerEngine(new NewSpiderStackOverflow()))
    ->run();
