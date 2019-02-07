<?php

use Phrawl\Crawler\AbstractBaseCrawler;
use Phrawl\Request\RequestFactory;
use Phrawl\Request\Types\RequestInterface;
use Symfony\Component\DomCrawler\Crawler;

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

    public function parser(Crawler $crawler, RequestInterface $request)
    {
        print $request->getUri().PHP_EOL;
        print $crawler->filter('title')->text().PHP_EOL;

        if ($this->first === true) {
            foreach ($crawler->filter('.sidebar-related .question-hyperlink')->links() as $link) {
                yield RequestFactory::new('GET', $link->getUri());
            }
        }
        $this->first = false;
    }
}

//try {
(new \Phrawl\CrawlerEngine(new NewSpiderStackOverflow()))
    ->run();
//} catch (Throwable $e) {
//    var_dump($e->getMessage());
//}
