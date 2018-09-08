<?php

namespace ZillowApi;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\XmlParseException;
use GuzzleHttp\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use ZillowApi\Model\Response;

/**
 * Zillow PHP API Client
 *
 * @author Chris Carrel <support@alquemie.net>
 */
class ZillowMortgageApiClient
{
    /**
     * @var string
     */
    protected $url = 'https://mortgageapi.zillow.com/';

    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $zwsid;

    /**
     * @var int
     */
    protected $responseCode = 0;

    /**
     * @var string
     */
    protected $responseMessage = null;

    /**
     * @var array
     */
    protected $response;

    /**
     * @var array
     */
    protected $results;

    /**
     * @var array
     */
    protected $photos = [];

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @var array
     *
     * Valid API functions
     */
    public static $validMethods = [
        'zillowLenderReviews',
    ];

    /**
     * @param string $zwsid
     * @param string|null $url
     */
    public function __construct($zwsid, $url = null)
    {
        $this->zwsid = $zwsid;

        if ($url) {
            $this->url = $url;
        }
    }

    /**
     * @return string
     */
    protected function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    protected function getZwsid()
    {
        return $this->zwsid;
    }

    /**
     * @param GuzzleClientInterface $client
     *
     * @return ZillowApiClient
     */
    public function setClient(GuzzleClientInterface $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return GuzzleClient
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->client = new GuzzleClient(
                [
                    'defaults' => [
                        'allow_redirects' => false,
                        'cookies'         => true
                    ]
                ]
            );
        }

        return $this->client;
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return Response
     */
    public function execute($name, $arguments)
    {
        if (!in_array($name, self::$validMethods)) {
            throw new ZillowException(sprintf('Invalid Zillow API method (%s)', $name));
        }

        return $this->doRequest($name, $arguments);
    }

    /**
     * @param string $call
     * @param array $params
     *
     * @return Response
     * @throws ZillowException
     */
    protected function doRequest($call, array $params)
    {
        if (!$this->getZwsid()) {
            throw new ZillowException('Missing ZWS-ID');
        }

        $response = $this->getClient()->get(
            $this->url . $call ,
            [
                'query' => array_merge(
                    ['partnerId' => $this->getZwsid()],
                    $params
                ),
            ]
        );

        return $this->parseResponse($call, $response);
    }

    /**
     * @param string $call
     * @param ResponseInterface $rawResponse
     *
     * @return Response
     */
    protected function parseResponse($call, ResponseInterface $rawResponse)
    {
        if ($rawResponse->getStatusCode() === '200') {
            try {
                $response = json_decode($rawResponse->getBody());
            } catch (XmlParseException $e) {
                $this->fail($rawResponse, true, $e);
            }
        } else {
            $this->fail($rawResponse, true);
        }

        return $response;
    }

    /**
     * @param Response $response
     * @param ResponseInterface $rawResponse
     * @param bool $logException
     * @param null $exception
     */
    private function fail(ResponseInterface $rawResponse, $logException = false, $exception = null)
    {
        if ($logException && $this->logger) {
            $this->logger->error(
                new \Exception(
                    sprintf(
                        'Failed Zillow call.  Status code: %s, Response string: %s',
                        $rawResponse->getStatusCode(),
                        (string) $rawResponse->getBody()
                    ),
                    0,
                    $exception
                )
            );
        }
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
