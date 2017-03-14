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
    public function onCreate(Model $account, array $questions)
    {
        $payload = $this->createCustomerPayloadFor($account, $questions);
        return $this->saveCustomer($payload);
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
    public function onUpdate(Model $account, $externalId, array $changeSet, array $questions)
    {
        $customer = $this->retrieveCustomerFor($account, $externalId);
        if (null === $customer) {
            return $this->onCreate($account, $questions);
        }
        // Get the comprehensive customer response (to ensure consistent data).
        $customer = $this->lookupCustomer($customer['Id']);
        $radixId  = $this->extractExternalIdFrom($customer);

        // @todo If a Radix ID is present, should a "two-way" push be executed - e.g. update the Radix data as well?
        // @todo Should this only send the current changeset, not the entire model...??
        $payload  = $this->createCustomerPayloadFor($account, $questions);

        if (empty($radixId)) {
            // Add the Omeda customer id if a Radix id is currently missing from the customer.
            // This ensures that future update requests will use the radix identifier.
            $payload['OmedaCustomerId'] = $customer['Id'];
        }
        $processor = $this->saveCustomer($payload);
        return $customer['Id'];
    }

    /**
     * Applies address information from the account model to the Omeda customer payload.
     *
     * @param   Model   $account
     * @param   array   $payload
     * @return  array
     */
    private function applyAddressesFor(Model $account, array $payload)
    {
        $map        = [
            'Company'       => 'companyName',
            'Street'        => 'street',
            'ExtraAddress'  => 'extra',
            'City'          => 'city',
            'RegionCode'    => 'regionCode',
            'PostalCode'    => 'postalCode',
            'CountryCode'   => 'countryCode',
        ];

        $addresses = [];
        foreach ($account->get('addresses') as $addressModel) {
            $address = [];
            foreach ($map as $theirKey => $ourKey) {
                $value = $addressModel->get($ourKey);
                if (null === $value) {
                    if ('companyName' === $ourKey) {
                        $value = $account->get($ourKey);
                        if (null === $value) {
                            continue;
                        }
                    } else {
                        continue;
                    }
                }
                $address[$theirKey] = $value;
            }
            if (empty($address)) {
                continue;
            }
            $addresses[] = $address;
        }
        if (!empty($addresses)) {
            $payload['Addresses'] = $addresses;
        }
        return $payload;
    }

    /**
     * Applies demographic answer information from the account model to the Omeda customer payload.
     *
     * @param   Model   $account
     * @param   array   $payload
     * @param   Model[] $questions
     * @return  array
     */
    private function applyAnswersFor(Model $account, array $payload, array $questions)
    {
        if (empty($questions)) {
            return $payload;
        }

        $answers = [];
        foreach ($account->get('answers') as $answerModel) {
            $questionModel = $answerModel->get('question');
            $value         = $answerModel->get('value');

            if (null === $value || null === $questionModel) {
                continue;
            }

            if ('related-choice-single' === $questionModel->get('questionType')) {
                // A related choice. Must use the answer's question instead.
                $questionModel = $value->get('question');
            }

            if (null === $questionModel || !isset($questions[$questionModel->getId()])) {
                continue;
            }
            if (null === $pull = $questionModel->get('pull')) {
                continue;
            }
            if (null === $externalId = $pull->get('identifier')) {
                continue;
            }

            $answer = [
                'OmedaDemographicId' => (integer) $externalId,
            ];

            switch ($questionModel->get('questionType')) {
                case 'choice-single':
                    $answer['OmedaDemographicValue'] = $value->get('integration')->get('pull')->get('identifier');
                    break;
                case 'choice-multiple':
                    $values = [];
                    foreach ($value as $choice) {
                        $values[] = $choice->get('integration')->get('pull')->get('identifier');
                    }
                    $answer['OmedaDemographicValue'] = $values;
                    break;
                default:
                    $answer['OmedaDemographicValue'] = $value;
                    break;
            }

            $answers[] = $answer;
        }
        if (!empty($answers)) {
            $payload['CustomerDemographics'] = $answers;
        }
        return $payload;
    }

    /**
     * Applies attribute information from the account model to the Omeda customer payload.
     *
     * @param   Model   $account
     * @param   array   $payload
     * @return  array
     */
    private function applyAttributesFor(Model $account, array $payload)
    {
        $map        = [
            'Salutation'    => 'salutation',
            'FirstName'     => 'givenName',
            'MiddleName'    => 'middleName',
            'LastName'      => 'familyName',
            'Suffix'        => 'suffix',
            'Title'         => 'title',
        ];
        foreach ($map as $theirKey => $ourKey) {
            $value = $account->get($ourKey);
            if (null === $value) {
                continue;
            }
            $payload[$theirKey] = $value;
        }

        $gender = $account->get('gender');
        switch ($gender) {
            case 'Male':
                $payload['Gender'] = 'M';
                break;
            case 'Female':
                $payload['Gender'] = 'F';
                break;
        }
        return $payload;
    }

    /**
     * Applies email information from the account model to the Omeda customer payload.
     *
     * @param   Model   $account
     * @param   array   $payload
     * @return  array
     */
    private function applyEmailsFor(Model $account, array $payload)
    {
        $typeMap = array_flip($this->getEmailTypeMap());

        $emails = [];
        foreach ($this->getRelatedEmails($account) as $emailModel) {
            $email = [
                'EmailAddress' => $emailModel->get('value')
            ];
            $type = $emailModel->get('emailType');
            if (isset($typeMap[$type])) {
                $email['EmailContactType'] = $typeMap[$type];
            }
            $emails[] = $email;
        }
        if (!empty($emails)) {
            $payload['Emails'] = $emails;
        }
        return $payload;
    }

    /**
     * Applies phone information from the account model to the Omeda customer payload.
     *
     * @param   Model   $account
     * @param   array   $payload
     * @return  array
     */
    private function applyPhonesFor(Model $account, array $payload)
    {
        $typeMap = array_flip($this->getPhoneTypeMap());

        $phones = [];
        foreach ($account->get('phones') as $phoneModel) {
            $phone = [
                'Number' => $phoneModel->get('number')
            ];
            $type = $phoneModel->get('phoneType');
            if (isset($typeMap[$type])) {
                $phone['PhoneContactType'] = $typeMap[$type];
            }
            $phones[] = $phone;
        }
        if (!empty($phones)) {
            $payload['Phones'] = $phones;
        }
        return $payload;
    }

    /**
     * Creates the payload for an Omeda customer insert/update.
     *
     * @param   Model   $account
     * @param   Model[] $questions
     * @return  array
     */
    private function createCustomerPayloadFor(Model $account, array $questions)
    {
        $payload = [
            'ExternalCustomerId'            => $account->getId(),
            'ExternalCustomerIdNamespace'   => 'radix',
        ];
        $payload = $this->applyAttributesFor($account, $payload);
        $payload = $this->applyEmailsFor($account, $payload);
        $payload = $this->applyAddressesFor($account, $payload);
        $payload = $this->applyPhonesFor($account, $payload);
        $payload = $this->applyAnswersFor($account, $payload, $questions);
        return $payload;
    }

    /**
     * This is needed in order to ensure newly created emails are also accounted for.
     * Modlr really needs to "automatically" append new inverse models to the owner's collection.
     *
     * @param   Model   $account
     * @param   Model[]
     */
    private function getRelatedEmails(Model $account)
    {
        $emails = [];
        foreach ($account->getStore()->getModelCache()->getAllForType('identity-account-email') as $email) {
            if (null === $email->get('account')) {
                continue;
            }
            if ($email->get('account')->getId() === $account->getId()) {
                $emails[$email->getId()] = $email;
            }
        }
        foreach ($account->get('emails') as $email) {
            if (!isset($emails[$email->getId()])) {
                $emails[$email->getId()] = $email;
            }
        }
        return $emails;
    }

    /**
     * Attempts to retrieve an Omeda customer for the provided Radix identity.
     *
     * @param   Model           $identity   The identity.
     * @param   string|null     $externalId The external (Omeda) ID last used when this identity was pushed.
     * @return  array|null  The Omeda customer API response, or null if no customer found.
     */
    private function retrieveCustomerFor(Model $identity, $externalId, $tryEmail = false)
    {
        $tryEmail = (boolean) $tryEmail;
        try {
            // Attempt to find Omeda customer using the Radix identity ID.
            return $this->lookupCustomerByRadixId($identity->getId());
        } catch (ClientException $e) {
            if (404 == $e->getCode()) {
                // No customer with a radix identifier found.
                if (empty($externalId)) {
                    // No external identifier was previously set.
                    return;
                }

                try {
                    // Attempt to find the customer using the standard Omeda ID.
                    return $this->lookupCustomerById($externalId);
                } catch (ClientException $e) {
                    if (404 == $e->getCode()) {
                        // Unable to find Omeda customer using the provided Omeda ID.
                        if (false === $tryEmail) {
                            return;
                        }
                        // Try lookup by email address.
                        throw new \BadMethodCallException(sprintf('%s: Lookup by email address is NYI.', __METHOD__));
                    } else {
                        throw $e;
                    }
                }
            } else {
                throw $e;
            }
        }
    }
}
