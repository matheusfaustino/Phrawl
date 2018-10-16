<?php

namespace Phpcrawler;

use Symfony\Component\DomCrawler\Crawler;
use Phpcrawler\Request;
use GuzzleHttp\Psr7\Response as GuzzleResponse;

/**
 * Class Response
 *
 * @package Phpcrawler
 */
class Response
{
    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * @var GuzzleResponse
     */
    private $response;

    /**
     * @var Request
     */
    private $request;

    /**
     * Response constructor.
     *
     * @param Crawler        $crawler
     * @param GuzzleResponse $response
     * @param Request        $request
     */
    public function __construct(Crawler $crawler, GuzzleResponse $response, Request $request)
    {
        $this->crawler = $crawler;
        $this->response = $response;
        $this->request = $request;
    }

    /**
     * @return Crawler
     */
    public function getCrawler(): Crawler
    {
        return $this->crawler;
    }

    /**
     * @return GuzzleResponse
     */
    public function getResponse(): GuzzleResponse
    {
        return $this->response;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Returns data persisted through request
     *
     * @todo I don't know if this is the right way
     *
     * @return array
     */
    public function getMetaData(): array
    {
        return $this->request->getMeta();
    }
}