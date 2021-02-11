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
 * Class PantherRequestRequestHandler
 *
 * @package Phrawl\Request\Handlers
 */
final class PantherRequestRequestHandler implements RequestHandlerInterface
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
     * PantherRequestRequestHandler constructor.
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
     * @todo try to keep the window open after the parallel, so I can manage the window
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

        $promise = call(function () use ($request) {
            /* DOMDocument cant be serialize */
            $panther = yield call(parallel(function () use ($request) {
                $clientLocal = Client::createChromeClient();

                $crawler = $clientLocal->request($request->getMethod(), $request->getUri());

                $waitFor = $request->getWaitFor();
                $waitFor and $clientLocal->waitFor($waitFor);

                return [$clientLocal->getPageSource()];
            }, $this->pool));

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
