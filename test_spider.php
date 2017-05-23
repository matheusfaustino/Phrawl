<?php
require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;


$spider = new \Phpcrawler\Spider();
$spider->setStartUrl('http://lespepitestech.com/');

$spider->setFullfilledRequestFunction(function (\GuzzleHttp\Psr7\Response $response) use($spider) {
    $crawler = new Crawler(null, 'http://lespepitestech.com/');
    $crawler->addContent($response->getBody(), 'text/html');

    yield sprintf("%s\n", $crawler->filter('title')->getNode(0)->textContent);

    $urls = $crawler->filter('.startup-entry .s-e-img a')->each(function(Crawler $node) {
        $href = $node->getNode(0)->getAttribute('href');
        $url = sprintf('%s%s', 'http://lespepitestech.com', $href);

        printf("%s\n", $url);
        return $url;
//        yield (new \GuzzleHttp\Promise\Promise())->resolve(new \GuzzleHttp\Psr7\Request('GET', $url));
    });

    foreach ($urls as $url) {
        yield ['request' => $spider->getClient()->getAsync($url), 'callback' => subRequest()];
    }
});


$spider->run();


function subRequest() {
    return function (\GuzzleHttp\Psr7\Response $response) {
        $crawler = new Crawler(null, 'http://lespepitestech.com/');
        $crawler->addContent($response->getBody(), 'text/html');

//        for($i=0; $i < 50; $i++)
//            yield sprintf("------------- %s\n", $crawler->filter('title')->getNode(0)->textContent);

        yield sprintf("Local subRequest: %s\n", $crawler->filter('title')->getNode(0)->textContent);
    };
}
