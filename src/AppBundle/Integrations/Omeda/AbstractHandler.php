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
    final public function supportsServiceClass($className)
    {
        return 'AppBundle\Integrations\Omeda\OmedaService';
    }

    /**
     * @return  ApiClient
     * @throws  \RuntimeException If the Omeda integration service has not been set.
     */
    final protected function getApiClient()
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
    final protected function getBrandData()
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
     * Gets the brand demographic data from Omeda.
     *
     * @return  array
     */
    final protected function getDemographicData(array $filterBy = [])
    {
        $demographics = [];
        $brandData    = $this->getBrandData();
        if (!isset($brandData['Demographics']) || !is_array($brandData['Demographics'])) {
            return $demographics;
        }
        $filter = !empty($filterBy);
        foreach ($brandData['Demographics'] as $demographic) {
            $identifier = $demographic['Id'];
            if (false === $filter || isset($filterBy[$identifier])) {
                $demographics[$identifier] = $demographic;
            }
        }
        return $demographics;
    }

    /**
     * Gets the internal question type for an Omeda demographic type.
     *
     * @param   int     $omedaType
     * @return  string
     * @throws  \InvalidArgumentException
     */
    final protected function getQuestionTypeFor($omedaType)
    {
        $map = [
            1  => 'choice-single',
            2  => 'choice-multiple',
            3  => 'string',
            5  => 'boolean',
            6  => 'datetime',
            7  => 'integer',
            8  => 'float',
        ];
        if (!isset($map[$omedaType])) {
            throw new \InvalidArgumentException('No corresponding question type was found for Omeda demographic type `%s`', $omedaType);
        }
        return $map[$omedaType];
    }

    /**
     * Parses an Omeda API response.
     *
     * @param   Response    $response
     * @return  array
     */
    final protected function parseApiResponse(Response $response)
    {
        $payload = @json_decode($response->getBody()->getContents(), true);
        if (!is_array($payload)) {
            throw new \RuntimeException('Unable to parse API response');
        }
        return $payload;
    }
}
