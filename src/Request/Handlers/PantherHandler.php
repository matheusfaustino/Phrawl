<?php

namespace Phrawl\Request\Handlers;

use function Amp\call;
use Amp\Parallel\Worker\DefaultPool;
use Amp\Parallel\Worker\Pool;
use function Amp\ParallelFunctions\parallel;
use Amp\Promise;
use Facebook\WebDriver\WebDriverKeys;
use Phrawl\Request\Types\PantherRequest;
use Phrawl\Request\Types\RequestInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\Client;

/**
 * Class PantherHandler
 *
 * @package Phrawl\Request\Handlers
 */
final class PantherHandler implements HandlerInterface
{
    /**
     * It is fixed because panther does not work well with more than one client at the same time
     */
    private const WORKER_COUNT = 1;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var Client
     */
    private $client;

    /**
     * PantherHandler constructor.
     *
     * @param null|Pool $pool
     */
    public function __construct(?Pool $pool = null)
    {
        $this->pool = $pool ?? new DefaultPool(self::WORKER_COUNT);
    }

    /**
     * Handle request object
     *
     * @todo should this method return the client and the crawler?
     * @todo verify the behavior of the client object after the function resolves
     *
     * @param RequestInterface $request
     *
     * @return Promise|null
     */
    public function handle(RequestInterface $request): ?Promise
    {
        if ( ! ($request instanceof PantherRequest)) {
            return null;
        }

        $this->client = $this->client ?? Client::createChromeClient();

        $promise = call(function () use ($request) {
            $clientLocal = null;
            /* DOMDocument cant be serialize */
            $panther = yield call(parallel(function () use ($request, &$clientLocal) {
                $this->client->getWebDriver()->getKeyboard()->sendKeys([
                    WebDriverKeys::CONTROL,
                    't',
                ]);
                $crawler = $this->client->request($request->getMethod(), $request->getUri());
                $clientLocal = $crawler;
//                var_dump($this->client->getWebDriver()->getSessionID());

                $waitFor = $request->getWaitFor();
                $waitFor and $this->client->waitFor($waitFor);

                return [$this->client->getPageSource()];
            }, $this->pool));

//            var_dump($clientLocal);

            $crawlerSf = new Crawler($panther[0], $request->getUri());

            return [
                $crawlerSf,
                $request,
                $this->client,
            ];
        });

        return $promise;
    }
}
