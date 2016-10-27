<?php

namespace AppBundle\Integrations\Omeda;

use AppBundle\Integration\Handler\AbstractHandler as BaseAbstractHandler;
use As3\OmedaSDK\ApiClient;
use GuzzleHttp\Psr7\Response;

class AbstractHandler extends BaseAbstractHandler
{
    /**
     * @var array
     */
    private $brandData = [];

    /**
     * {@inheritdoc}
     */
    public function supportsServiceClass($className)
    {
        return 'AppBundle\Integrations\Omeda\OmedaService';
    }

    /**
     * @return  ApiClient
     * @throws  \RuntimeException If the Omeda integration service has not been set.
     */
    protected function getApiClient()
    {
        if (null === $this->service) {
            throw new \RuntimeException('No service has been set to this handler.');
        }
        return $this->service->getApiClient();
    }

    /**
     * Gets the brand data from Omeda, if not already loaded in memory.
     *
     * @return  array
     */
    protected function getBrandData()
    {
        $config = $this->getApiClient()->getConfiguration();
        $client = $config['clientKey'];
        $brand  = $config['brandKey'];

        if (!isset($this->brandData[$client][$brand])) {
            $this->brandData[$client][$brand] = $this->parseApiResponse($this->getApiClient()->brand->lookup());
        }
        return $this->brandData[$client][$brand];
    }

    /**
     * Parses an Omeda API response.
     *
     * @param   Response    $response
     * @return  array
     */
    protected function parseApiResponse(Response $response)
    {
        $payload = @json_decode($response->getBody()->getContents(), true);
        if (!is_array($payload)) {
            throw new \RuntimeException('Unable to parse API response');
        }
        return $payload;
    }
}
