<?php

use Phrawl\BaseCrawler;
use Phrawl\ProcessorPoolRequest;
use Phrawl\Request;
use Symfony\Component\DomCrawler\Crawler;

require __DIR__.'/../vendor/autoload.php';

final class SpiderStackOverflow extends BaseCrawler
{
    public $name = 'stackoverflow';

    protected $configs = ['concurrency' => 5];

    public $start_urls
        = [
            'https://stackoverflow.com/questions/10720325/selenium-webdriver-wait-for-complex-page-with-javascriptjs-to-load',
            'https://stackoverflow.com/questions/9291898/selenium-wait-for-javascript-function-to-execute-before-continuing',
            'https://stackoverflow.com/questions/23050430/does-selenium-wait-for-javascript-to-complete',
            'https://stackoverflow.com/questions/835501/how-do-you-stash-an-untracked-file',
            'https://stackoverflow.com/questions/5355121/passing-dict-to-constructor/5355152#5355152',
        ];

    public function parser(\Phrawl\Response $response)
    {
        $crawler = $response->getCrawler();
        printf("Title: %s \nQuestion: %s \n\n", $crawler->filterXPath('//title')->text()
            , $crawler->filterXPath('//a[@class="question-hyperlink"]')->text());

        $url = $crawler->evaluate('//div[contains(@class, "module")]/div/div/a[@class="question-hyperlink"]')->first();
        $url = $url->getBaseHref().$url->attr('href');

        yield new Request($url, 'first');
    }

    public function first(\Phrawl\Response $response)
    {
        printf("First method -- Title: %s \n\n", $response->getCrawler()->filterXPath('//title')->text());
    }
}

// To run as a standalone script:
//(new ProcessorPoolRequest(new SpiderStackOverflow()))
//    ->setDefaultLoggerLevel(\Monolog\Logger::INFO)
//    ->run();
