<?php declare(strict_types=1);

namespace FeedIo\Adapter\Guzzle;

use FeedIo\Adapter\ClientInterface;
use FeedIo\Adapter\NotFoundException;
use FeedIo\Adapter\ResponseInterface;
use FeedIo\Adapter\ServerErrorException;
use GuzzleHttp\Exception\BadResponseException;

/**
 * Guzzle dependent HTTP client
 */
class Client implements ClientInterface
{

    /**
     * Default user agent provided with the package
     */
    const DEFAULT_USER_AGENT = 'Mozilla/5.0 (X11; U; Linux i686; fr; rv:1.9.1.1) Gecko/20090715 Firefox/3.5.1';

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $guzzleClient;

    /**
     * @var string
     */
    protected $userAgent;

    /**
     * @param \GuzzleHttp\ClientInterface $guzzleClient
     * @param string $userAgent
     */
    public function __construct(\GuzzleHttp\ClientInterface $guzzleClient, string $userAgent = self::DEFAULT_USER_AGENT)
    {
        $this->guzzleClient = $guzzleClient;
        $this->userAgent = $userAgent;
    }

    /**
     * @param  string $userAgent The new user-agent
     * @return Client
     */
    public function setUserAgent(string $userAgent) : Client
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * @param string $url
     * @param \DateTime $modifiedSince
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getResponse(string $url, \DateTime $modifiedSince) : ResponseInterface
    {
        $start = microtime(true);
        try {
            $options = $this->getOptions($modifiedSince);
            $psrResponse = $this->guzzleClient->request('get', $url, $options);
            $duration = $this->getDuration($start);
            return new Response($psrResponse, $duration);
        } catch (BadResponseException $e) {
            $duration = $this->getDuration($start);
            switch ((int) $e->getResponse()->getStatusCode()) {
                case 404:
                    $notFoundException = new NotFoundException($e->getMessage());
                    $notFoundException->setDuration($duration);
                    throw $notFoundException;
                default:
                    $serverErrorException = new ServerErrorException($e->getMessage());
                    $serverErrorException->setResponse($e->getResponse());
                    $serverErrorException->setDuration($duration);
                    throw $serverErrorException;
            }
        }
    }

    /**
     * @param float $start
     * @return int
     */
    protected function getDuration(float $start): int
    {
        return intval(round(microtime(true) - $start, 3) * 1000);
    }

    /**
     * @param \DateTime $modifiedSince
     * @return array
     */
    protected function getOptions(\DateTime $modifiedSince) : array
    {
        return [
            'headers' => [
                'Accept-Encoding' => 'gzip, deflate',
                'User-Agent' => $this->userAgent,
                'If-Modified-Since' => $modifiedSince->format(\DateTime::RFC2822)
            ]
        ];
    }
}
