<?php

namespace AppBundle\Integrations\Omeda;

use AppBundle\Integration\Handler\AccountPushInterface;
use As3\Modlr\Models\Model;
use GuzzleHttp\Exception\ClientException;

class AccountPushHandler extends AbstractHandler implements AccountPushInterface
{
    /**
     * {@inheritdoc}
     */
    public function onCreate(Model $account)
    {
        var_dump(__METHOD__);
        die();
    }

    /**
     * {@inheritdoc}
     */
    public function onDelete(Model $account)
    {
        // Omeda does not support a delete API method. Do nothing.
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function onUpdate(Model $account, array $changeset)
    {
        // Customer lookup dance:
        //  - Lookup customer by radix id: if found, do update; if not found, continue
        //  - Lookup customer by integration push meta (push.[].integration = this.integration.id)
        //      - No meta found? Do insert.
        //      - Lookup customer by the push.[].identifier
        //      - Customer found? Do update and append radix id. If not found, do insert.
        try {
            $customer = $this->lookupCustomerFor($account->getId());
            // Customer found.
            $this->doUpdate($account, $changeset);
        } catch (ClientException $e) {
            if (404 == $e->getCode()) {
                // No customer with a radix identifier found.
                // Must now check external id...
                // @todo Generally speaking, this is here for compatibility with legacy Cygnus implementations and should be removed.
                $identifier = null;
                foreach ($account->get('externalIds') as $external) {
                    if ('omeda' === $external->get('source') && is_numeric($external->get('identifier'))) {
                        $identifier = $external->get('identifier');
                    }
                }
                if ($identifier) {
                    // Attempt to find the customer using the standard Omeda id.
                    try {
                        $customer = $this->lookupCustomerById($identifier);
                        $this->doUpdate($account, $changeset, true);
                    } catch (ClientException $e) {
                        if (404 == $e->getCode()) {
                            // Omeda customer identifier is invalid. Treat as a new record and insert.
                            $this->doInsert($account);
                        } else {
                            throw $e;
                        }
                    }

                    var_dump($identifier);
                    die();
                } else {
                    // No legacy external id found. Treat as a new record and insert.
                    $this->doInsert($account);
                }

            } else {
                throw $e;
            }
        }
    }

    private function doInsert(Model $account)
    {
        var_dump(__METHOD__);
        die();
        // Check if this customer
    }

    private function doUpdate(Model $account, array $changeset, $appendRadixId = false)
    {
        var_dump(__METHOD__, $appendRadixId);
        die();
    }

    private function lookupCustomerById($customerId)
    {
        return $this->parseApiResponse(
            $this->getApiClient()->customer()->lookupById($customerId)
        );
    }

    private function lookupCustomerFor($accountId)
    {
        return $this->parseApiResponse(
            $this->getApiClient()->customer()->lookupByExternalId('radix', $accountId)
        );
    }
}
