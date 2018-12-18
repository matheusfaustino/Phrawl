## Phrawl: A web crawling framework in PHP (or it seems so)

Phrawl is a lib aiming to be a web crawling framework for PHP. It was created with the idea of joining all the good libs around PHP in one place to help those who want to crawl the internet. It's inspired by Scrapy.

It is a WIP lib, so it lacks some features like integration with a webdriver (as Selenium) and an async I/O. 

### Installing
`$ composer global require matheusfaustino/phrawl:dev-master`

#### Example
```
$ phrawl <<EOF
<?php
class SpiderStackOverflow extends Phrawl\BaseCrawler
{
    public \$name = 'stackoverflow';
    protected \$configs = ['concurrency' => 5];
    public \$start_urls
        = [
            'https://stackoverflow.com/questions/10720325/selenium-webdriver-wait-for-complex-page-with-javascriptjs-to-load',
            'https://stackoverflow.com/questions/9291898/selenium-wait-for-javascript-function-to-execute-before-continuing',
            'https://stackoverflow.com/questions/23050430/does-selenium-wait-for-javascript-to-complete',
            'https://stackoverflow.com/questions/835501/how-do-you-stash-an-untracked-file',
            'https://stackoverflow.com/questions/5355121/passing-dict-to-constructor/5355152#5355152',
        ];
    public function parser(\Phrawl\Response \$response)
    {
        \$crawler = \$response->getCrawler();
        printf("Title: %s \nQuestion: %s \n\n", \$crawler->filterXPath('//title')->text()
            , \$crawler->filterXPath('//a[@class="question-hyperlink"]')->text());
        \$url = \$crawler->evaluate('//div[contains(@class, "module")]/div/div/a[@class="question-hyperlink"]')->first();
        \$url = \$url->getBaseHref().\$url->attr('href');
        yield new \Phrawl\Request(\$url, 'first');
    }
    public function first(\Phrawl\Response \$response)
    {
        printf("First method -- Title: %s \n\n", \$response->getCrawler()->filterXPath('//title')->text());
    }
}
EOF
```

I will add more examples soon...
