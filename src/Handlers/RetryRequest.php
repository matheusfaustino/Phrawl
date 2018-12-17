<?php

namespace Phrawl\Handlers;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;

class RetryRequest
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $maxRetries;

    /**
     * RetryRequest constructor.
     *
     * @param LoggerInterface $logger
     * @param int             $retries
     */
    public function __construct(LoggerInterface $logger, int $retries = 2)
    {
        $this->logger = $logger;
        $this->maxRetries = $retries;
    }

    /**
     * @see https://gist.github.com/gunnarlium/665fc1a2f6dd69dfba65
     *
     * @return callable
     */
    public function retry(): callable
    {
        return function (
            int $retries,
            Request $request,
            Response $response = null,
            RequestException $exception = null
        ): bool {
            if ($retries >= $this->maxRetries) {
                return false;
            }
            if ( ! ($this->connectionError($exception) || $this->serverError($response))) {
                return false;
            }

            $this->logger->warning(
                \sprintf(
                    'Retrying request %s %s %s/%s, %s',
                    $request->getMethod(),
                    $request->getUri(),
                    $retries + 1,
                    $this->maxRetries,
                    $response ? 'status code: '.$response->getStatusCode() : $exception->getMessage()
                ),
                [$request->getHeader('Host')[0]]
            );

            return true;
        };
    }

    /**
     * @param RequestException|null $exception
     *
     * @return bool
     */
    private function connectionError(RequestException $exception = null): bool
    {
        return $exception instanceof ConnectException;
    }

    /**
     * @param Response|null $response
     *
     * @return bool
     */
    private function serverError(Response $response = null): bool
    {
        return $response && $response->getStatusCode() >= 500;
    }
}