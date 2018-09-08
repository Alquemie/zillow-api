<?php

namespace ZillowApi;

use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
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
            $this->client = new Client(
                [
                    'base_uri' => $this->url,
                    'allow_redirects' => false,
                    'cookies'         => true
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
            throw new ZillowException('Missing Partner-ID');
        }
        $result = array();  // remove later

        $response = $this->getClient()->get(
            $call,
            [
                'query' => array_merge(
                    ['partnerId' => $this->getZwsid()],
                    $params
                ),
            ]
        );
    
        if ($response->getStatusCode() == '200') {
            try {
                $result = json_decode($response->getBody(true)->getContents());
            } catch (Exception $e) {
                throw new ZillowException('Zillow Error: #%d: %s', $e->getCode(), $e->getMessage());
            }
        } else {
            throw new ZillowException('Zillow Response Error: ' . print_r($response,true));
        }

        return $result;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
