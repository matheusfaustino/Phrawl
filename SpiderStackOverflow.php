<?php

use Phpcrawler\BaseCrawler;
use Phpcrawler\ProcessorPoolRequest;
use Phpcrawler\Request;
use Symfony\Component\DomCrawler\Crawler;

require __DIR__.'/vendor/autoload.php';

final class SpiderStackOverflow extends BaseCrawler
{
    protected $name = 'stackoverflow';

    protected $configs = ['concurrency' => 2];

    public $start_urls
        = [
            'https://stackoverflow.com/questions/10720325/selenium-webdriver-wait-for-complex-page-with-javascriptjs-to-load',
            'https://stackoverflow.com/questions/9291898/selenium-wait-for-javascript-function-to-execute-before-continuing',
            'https://stackoverflow.com/questions/23050430/does-selenium-wait-for-javascript-to-complete',
            'https://stackoverflow.com/questions/835501/how-do-you-stash-an-untracked-file',
            'https://stackoverflow.com/questions/5355121/passing-dict-to-constructor/5355152#5355152',
        ];

    public function parser(Crawler $response)
    {
        printf("Title: %s \nQuestion: %s \n\n", $response->filterXPath('//title')->text()
            , $response->filterXPath('//a[@class="question-hyperlink"]')->text());

        $url = $response->evaluate('//div[contains(@class, "module")]/div/div/a[@class="question-hyperlink"]')->first();
        $url = $url->getBaseHref().$url->attr('href');

        yield new Request($url, 'first');
    }

    public function first(Crawler $response)
    {
        printf("First method -- Title: %s \n\n", $response->filterXPath('//title')->text());
    }
}

(new ProcessorPoolRequest(new SpiderStackOverflow()))->run();