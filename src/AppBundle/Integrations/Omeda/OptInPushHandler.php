<?php

namespace AppBundle\Integrations\Omeda;

use AppBundle\Integration\Handler\OptInPushInterface;
use GuzzleHttp\Exception\ClientException;

class OptInPushHandler extends AbstractHandler implements OptInPushInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($externalId, $emailAddress, $optedIn, array $extra = [])
    {
        $products = $this->getProductData([$externalId => true]);
        if (empty($products)) {
            throw new \InvalidArgumentException(sprintf('No Omeda product found for id `%s', $externalId));
        }
        $product = reset($products);

        if (isset($product['ProductType']) && 2 == $product['ProductType']) {
            $this->updateProductStatus($emailAddress, $product['Id'], $optedIn);
        }

        if (isset($product['DeploymentTypeId'])) {
            $this->updateFilterFor($emailAddress, $product['DeploymentTypeId'], $optedIn);
        }
    }

    /**
     * Updates the product's receive status for the provided email address.
     *
     * @param   string  $emailAddress
     * @param   int     $productId
     * @param   bool    $optedIn
     */
    private function updateProductStatus($emailAddress, $productId, $optedIn)
    {
        try {
            $response = $this->lookupCustomerByEmail($emailAddress);
            if (isset($response['Customers']) && is_array($response['Customers'])) {
                foreach ($response['Customers'] as $customer) {
                    $payload = [
                        'OmedaCustomerId' => $customer['Id'],
                        'Products'        => [
                            [
                                'OmedaProductId' => (integer) $productId,
                                'Receive'        => (integer) (boolean) $optedIn,
                            ],
                        ],
                    ];
                    $response = $this->saveCustomer($payload, false);
                }
            }
        } catch (ClientException $e) {
            if (404 == $e->getCode()) {
                // Do we do an insert here?
                return;
            }
            throw $e;
        }
    }
}
