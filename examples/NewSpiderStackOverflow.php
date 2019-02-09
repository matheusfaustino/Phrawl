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

    private $first = true;

    protected $startUrls = 'https://stackoverflow.com/questions/23050430/does-selenium-wait-for-javascript-to-complete';
//        = [
//            'https://stackoverflow.com/questions/10720325/selenium-webdriver-wait-for-complex-page-with-javascriptjs-to-load',
//            'https://stackoverflow.com/questions/9291898/selenium-wait-for-javascript-function-to-execute-before-continuing',
//            'https://stackoverflow.com/questions/23050430/does-selenium-wait-for-javascript-to-complete',
//            'https://stackoverflow.com/questions/835501/how-do-you-stash-an-untracked-file',
//            'https://stackoverflow.com/questions/5355121/passing-dict-to-constructor/5355152#5355152',
//        ];

    public function parser(Crawler $crawler, RequestInterface $request, ?Client $pantherClient = null)
    {
//        var_dump('parser', $crawler->filterXPath('//title')->text());

//        if ($this->first === true) {
//            print $request->getUri().PHP_EOL;
//            print $crawler->filter('title')->text().PHP_EOL;

//        foreach ($crawler->filter('.sidebar-related .question-hyperlink')->links() as $link) {
        yield RequestFactory::newWebDriver('GET',
            'https://stackoverflow.com/questions/9291898/selenium-wait-for-javascript-function-to-execute-before-continuing',
            [], null,
            [$this, 'dynamic']);
        yield RequestFactory::newWebDriver('GET',
            'https://stackoverflow.com/questions/5355121/passing-dict-to-constructor/5355152#5355152', [], null,
            [$this, 'dynamic']);
        yield RequestFactory::newWebDriver('GET',
            'https://stackoverflow.com/questions/835501/how-do-you-stash-an-untracked-file', [], null,
            [$this, 'dynamic']);
//        }
//        }
//        $this->first = false;
    }

    public function dynamic(Crawler $crawler, RequestInterface $request, ?Client $pantherClient = null)
    {
        var_dump('dynamic', $crawler->filterXPath('//title')->text());
//        var_dump($request);
//        var_dump($pantherClient);
    }
}

//try {
(new \Phrawl\CrawlerEngine(new NewSpiderStackOverflow()))
    ->run();
//} catch (Throwable $e) {
//    var_dump($e->getMessage());
//}
