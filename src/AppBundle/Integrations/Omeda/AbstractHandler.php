<?php

namespace AppBundle\Integrations\Omeda;

use AppBundle\Integration\Handler\AbstractHandler as BaseAbstractHandler;
use As3\OmedaSDK\ApiClient;
use GuzzleHttp\Psr7\Response;

class AbstractHandler extends BaseAbstractHandler
{
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
