<?php

namespace AppBundle\Integrations\Omeda;

use AppBundle\Integration\Handler\AbstractHandler as BaseAbstractHandler;
use As3\OmedaSDK\ApiClient;
use GuzzleHttp\Exception\ClientException;
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
     * Determines if the provided Omeda customer object has the requested behavior id.
     *
     * @param   array   $customer
     * @param   int     $identifier
     * @return  bool
     */
    final protected function customerHasBehavior(array $customer, $identifier)
    {
        if (!isset($customer['Behavior']) || !is_array($customer['Behavior'])) {
            return false;
        }
        foreach ($customer['Behavior'] as $behavior) {
            if ($behavior['BehaviorId'] == $identifier) {
                return true;
            }
        }
        return false;
    }

    /**
     * Creates the Omeda identity behavior to add to the Omeda customer.
     *
     * @param   int         $identifier
     * @param   \DateTime   $createdDate
     * @param   string      $identityType
     * @return  array
     */
    final protected function createIdentityBehavior($identifier, \DateTime $createdDate, $identityType)
    {
        return [
            'BehaviorId' => $identifier,
            'BehaviorDate' => $createdDate->format('Y-m-d H:i:s'),
            'BehaviorPromoCode' => sprintf('Radix Type: %s', $identityType),
        ];
    }

    /**
     * Extracts an external id value from the provided customer for a namespace.
     *
     * @param   array   $customer
     * @param   string  $namespace
     * @return  string|null
     */
    final protected function extractExternalIdFrom(array $customer, $namespace = 'radix')
    {
        if (!isset($customer['ExternalIds']) || !is_array($customer['ExternalIds'])) {
            return;
        }
        foreach ($customer['ExternalIds'] as $externalId) {
            if (!isset($externalId['Namespace']) || !isset($externalId['Id'])) {
                continue;
            }
            if ($namespace === $externalId['Namespace']) {
                return $externalId['Id'];
            }
        }
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
     * @return  array
     */
    final protected function getEmailTypeMap()
    {
        return [ 300 => 'Business', 310 => 'Personal' ];
    }

    /**
     * @return  array
     */
    final protected function getPhoneTypeMap()
    {
        return [ 200 => 'Business', 210 => 'Home', 230 => 'Mobile', 240 => 'Fax' ];
    }

    /**
     * Gets the brand product data from Omeda.
     *
     * @return  array
     */
    final protected function getProductData(array $filterBy = [])
    {
        $products  = [];
        $brandData = $this->getBrandData();
        if (!isset($brandData['Products']) || !is_array($brandData['Products'])) {
            return $products;
        }
        $filter = !empty($filterBy);
        foreach ($brandData['Products'] as $product) {
            $identifier = $product['Id'];
            if (false === $filter || isset($filterBy[$identifier])) {
                $products[$identifier] = $product;
            }
        }
        return $products;
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
     * Looks up an Omeda customer by Omeda ID, with the comprehensive response.
     *
     * @param   string|int  $customerId
     * @return  array
     * @throws  ClientException     On response error (e.g. 404, etc).
     */
    final protected function lookupCustomer($customerId)
    {
        return $this->parseApiResponse(
            $this->getApiClient()->customer()->lookup($customerId)
        );
    }

    /**
     * Looks up an Omeda customer by email address.
     *
     * @param   string  $emailAddress
     * @return  array
     * @throws  ClientException     On response error (e.g. 404, etc).
     */
    final protected function lookupCustomerByEmail($emailAddress)
    {
        return $this->parseApiResponse(
            $this->getApiClient()->customer()->lookupByEmail($emailAddress)
        );
    }

    /**
     * Looks up an Omeda customer by Omeda ID.
     *
     * @param   string|int  $customerId
     * @return  array
     * @throws  ClientException     On response error (e.g. 404, etc).
     */
    final protected function lookupCustomerById($customerId)
    {
        return $this->parseApiResponse(
            $this->getApiClient()->customer()->lookupById($customerId)
        );
    }

    /**
     * Looks up an Omeda customer by the Radix account ID.
     *
     * @param   string  $accountId
     * @return  array
     * @throws  ClientException     On response error (e.g. 404, etc).
     */
    final protected function lookupCustomerByRadixId($accountId)
    {
        return $this->parseApiResponse(
            $this->getApiClient()->customer()->lookupByExternalId('radix', $accountId)
        );
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

    final protected function saveCustomer(array $payload, $runProcessor = true)
    {
        $response = $this->parseApiResponse(
            $this->getApiClient()->customer()->save($payload)
        );
        if (!isset($response['ResponseInfo'][0]['TransactionId'])) {
            throw new \RuntimeException('Unable to retrieve a transaction ID from the customer save response.');
        }
        $transactionId = $response['ResponseInfo'][0]['TransactionId'];
        if (false == $runProcessor) {
            return $transactionId;
        }

        $response = $this->parseApiResponse(
            $this->getApiClient()->utility()->runProcessor($transactionId)
        );
        if (!isset($response['BatchStatus'][0]['OmedaCustomerId'])) {
            throw new \RuntimeException('Unable to retrieve an Omeda customer ID from the processor result.');
        }
        return $response['BatchStatus'][0]['OmedaCustomerId'];
    }

    /**
     * Updates the opt in/out filter for the provided email address.
     *
     * @param   string  $emailAddress
     * @param   int     $deploymentTypeId
     * @param   bool    $optedIn
     * @return  array
     */
    final protected function updateFilterFor($emailAddress, $deploymentTypeId, $optedIn)
    {
        $omail = $this->getApiClient()->omail();
        if ($optedIn) {
            $response = $omail->optInDeployment($emailAddress, $deploymentTypeId);
        } else {
            $response = $omail->optOutDeployment($emailAddress, $deploymentTypeId);
        }
        return $this->parseApiResponse($response);
    }
}
