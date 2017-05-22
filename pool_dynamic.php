<?php
require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\{Client, Pool, Psr7\Request, Psr7\Response};
use Symfony\Component\DomCrawler\Crawler;

$client = new Client();
// to repetindo a URL só pra ser mais facil, estava testando
$queue = new Phpcrawler\QueueUrl(['http://lespepitestech.com/']);

$pool = new Pool($client, $queue->getQueue(), [
    // mais que 1, ele nao roda as outras requisicoes
    'concurrency' => 1,
    // seria legal que cada request pudesse escolher a funcao de callback
    'fulfilled' => function(Response $response, int $index) use ($queue) {
        $crawler = new Crawler(null, 'http://lespepitestech.com/');
        $crawler->addContent($response->getBody(), 'text/html');

        var_dump($crawler->filter('title')->getNode(0)->textContent);
        $crawler->filter('.startup-entry .s-e-img a')->each(function(Crawler $node, $i) use ($queue) {
            $href = $node->getNode(0)->getAttribute('href');

            // adiciona na fila (só para ver se ele deixa mexer no yield dinamicamente)
            $queue->addItemQueue(sprintf('%s%s', 'http://lespepitestech.com', $href));
        });
    },
    'rejected' => function ($reason, $index) {
        // rejeitou
    },
]);


// run
$pool->promise()->wait();
