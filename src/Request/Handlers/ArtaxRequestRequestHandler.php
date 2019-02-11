<?php

namespace Phrawl\Request\Handlers;

use Amp\Artax\DefaultClient;
use Amp\Artax\Request;
use Amp\Artax\Response;
use Amp\ByteStream\Message;
use function Amp\call;
use Amp\Promise;
use Amp\Socket\ClientTlsContext;
use Phrawl\Request\Types\ArtaxRequest;
use Phrawl\Request\Types\RequestInterface;
use Symfony\Component\DomCrawler\Crawler;

final class ArtaxRequestRequestHandler implements RequestHandlerInterface
{
    /**
     * @var DefaultClient
     */
    private $client;

    /**
     * ArtaxRequest constructor.
     *
     * @param DefaultClient|null $client
     */
    public function __construct(?DefaultClient $client = null)
    {
        $this->client = $client ?? new DefaultClient(null, null, (new ClientTlsContext())->withoutPeerVerification());
    }

    /**
     * Handle request object
     * Return `null` if it is not satisfies the handler or return a promise
     *
     * @param RequestInterface $request
     *
     * @return Promise|null
     */
    public function handle(RequestInterface $request): ?Promise
    {
        if ( ! ($request instanceof ArtaxRequest)) {
            return null;
        }

        return call(function () use ($request) {
            /* @var $response Response */
            $response = yield $this->client->request(
                (new Request($request->getUri(), $request->getMethod()))
                    ->withHeaders($request->getHeaders())
                    ->withBody($request->getBody())
            );

            /* @var $body Message */
            $body = yield $response->getBody();

            return [
                new Crawler($body, $response->getOriginalRequest()->getUri()),
                $request,
            ];
        });
    }
}
