<?php

namespace AppBundle\Integrations\Omeda;

use AppBundle\Integration\Definition\ExternalIdentityDefinition;
use AppBundle\Integration\Definition\IdentityAddressDefinition;
use AppBundle\Integration\Definition\IdentityAnswerDefinition;
use AppBundle\Integration\Definition\IdentityEmailDefinition;
use AppBundle\Integration\Handler\IdentifyInterface;

class IdentifyHandler extends AbstractHandler implements IdentifyInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($externalId, array $questionIds = [])
    {
        $this->validateIdentifier($externalId);
        $payload = $this->parseApiResponse($this->getApiClient()->customer->lookup($externalId));

        $definition = new ExternalIdentityDefinition();
        $this->applyAttributes($definition, $payload);
        $this->applyEmails($definition, $payload);
        $this->applyAddresses($definition, $payload);
        $this->applyAnswers($definition, $payload, $questionIds);
        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceAndIdentifierFor($externalId)
    {
        $this->validateIdentifier($externalId);
        $payload = $this->parseApiResponse($this->getApiClient()->customer->lookupById($externalId));
        if (!isset($payload['Id'])) {
            throw new \RuntimeException('No identifier found in the Omeda customer response.');
        }
        return [ $this->getSourceKey(), (string) $payload['Id'] ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceKey()
    {
        return sprintf('omeda:%s', strtolower($this->getApiClient()->getConfiguration()['brandKey']));
    }

    /**
     * Determines if the provided external id is valid.
     *
     * @param   string  $externalId
     * @return  bool
     */
    private function isIdentifierValid($externalId)
    {
        return is_numeric($externalId) || 1 === preg_match('/^[A-Z0-9]{15}$/', $externalId);
    }

    /**
     * Applies addresses from the payload to the definition.
     *
     * @param   ExternalIdentityDefinition  $definition
     * @param   array                       $payload
     */
    private function applyAddresses(ExternalIdentityDefinition $definition, array $payload)
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

        if (isset($payload['Addresses']) && is_array($payload['Addresses'])) {
            foreach ($payload['Addresses'] as $address) {
                $identifier = isset($address['Id']) ? $address['Id'] : null;
                $addressDef = new IdentityAddressDefinition($identifier);
                foreach ($map as $theirKey => $ourKey) {
                    if (isset($address[$theirKey]) && !empty($address[$theirKey])) {
                        $addressDef->getAttributes()->set($ourKey, $address[$theirKey]);
                    }
                }
                if (isset($address['StatusCode']) && 1 == $address['StatusCode']) {
                    $addressDef->getAttributes()->set('isPrimary', true);
                }
                $definition->addAddress($addressDef);
            }
        }
    }

    /**
     * Applies identity answers from the payload to the definition for the provided external question ids.
     *
     * @param   ExternalIdentityDefinition  $definition
     * @param   array                       $payload
     * @param   array                       $questionIds
     */
    private function applyAnswers(ExternalIdentityDefinition $definition, array $payload, array $questionIds)
    {
        if (!isset($payload['CustomerDemographics']) || !is_array($payload['CustomerDemographics'])) {
            // No answers to process.
            return;
        }

        $answers       = [];
        $demographics   = $this->getDemographicData(array_flip($questionIds));

        foreach ($payload['CustomerDemographics'] as $answer) {
            $identifier = $answer['DemographicId'];
            if (!isset($demographics[$identifier])) {
                continue;
            }
            $demographic  = $demographics[$identifier];
            $questionType = $this->getQuestionTypeFor($demographic['DemographicType']);

            switch ($questionType) {
                case 'choice-single':
                    $answers[$identifier] = $answer['ValueId'];
                    break;
                case 'choice-multiple':
                    if (!isset($answers[$identifier])) {
                        $answers[$identifier] = [];
                    } elseif (!is_array($answers[$identifier])) {
                        $answers[$identifier] = (array) $answers[$identifier];
                    }
                    $answers[$identifier][] = $answer['ValueId'];
                    break;
                case 'datetime':
                    $answers[$identifier] = $answer['ValueDate'];
                    break;
                default:
                    $answers[$identifier] = $answer['ValueText'];
                    break;
            }
        }

        foreach ($answers as $questionId => $value) {
            $definition->addAnswer(new IdentityAnswerDefinition($questionId, $value));
        }
    }

    /**
     * Applies identity attributes from the payload to the definition.
     *
     * @param   ExternalIdentityDefinition  $definition
     * @param   array                       $payload
     */
    private function applyAttributes(ExternalIdentityDefinition $definition, array $payload)
    {
        $attributes = $definition->getAttributes();
        $map        = [
            'Salutation'    => 'salutation',
            'FirstName'     => 'givenName',
            'MiddleName'    => 'middleName',
            'LastName'      => 'familyName',
            'Suffix'        => 'suffix',
            'Title'         => 'title',
        ];
        foreach ($map as $theirKey => $ourKey) {
            if (isset($payload[$theirKey]) && !empty($payload[$theirKey])) {
                $attributes->set($ourKey, $payload[$theirKey]);
            }
        }
        if (isset($payload['Gender'])) {
            $value  = $payload['Gender'];
            $gender = null;
            if ('F' === $value) {
                $gender = 'Female';
            } elseif ('M' === $value) {
                $gender = 'Male';
            }
            $attributes->set('gender', $gender);
        }
    }

    /**
     * Applies emails from the payload to the definition.
     *
     * @param   ExternalIdentityDefinition  $definition
     * @param   array                       $payload
     */
    private function applyEmails(ExternalIdentityDefinition $definition, array $payload)
    {
        $typeMap = [ 300 => 'Business', 310 => 'Personal' ];
        if (isset($payload['Emails']) && is_array($payload['Emails'])) {
            foreach ($payload['Emails'] as $email) {
                if (!isset($email['EmailAddress'])) {
                    continue;
                }
                $identifier = isset($email['Id']) ? $email['Id'] : null;
                try {
                    $emailDef = new IdentityEmailDefinition($email['EmailAddress'], $identifier);
                    if (isset($email['StatusCode']) && 1 == $email['StatusCode']) {
                        $emailDef->getAttributes()->set('isPrimary', true);
                    }
                    if (isset($email['EmailContactType']) && isset($typeMap[$email['EmailContactType']])) {
                        $emailDef->getAttributes()->set('emailType', $typeMap[$email['EmailContactType']]);
                    }
                    $definition->addEmail($emailDef);
                } catch (\Exception $e) {
                }
            }
        }
    }

    /**
     * Applies phones from the payload to the definition.
     *
     * @param   ExternalIdentityDefinition  $definition
     * @param   array                       $payload
     */
    private function applyPhones(ExternalIdentityDefinition $definition, array $payload)
    {
        $typeMap = [ 200 => 'Business', 210 => 'Home', 230 => 'Mobile', 240 => 'Fax' ];
        if (isset($payload['Phones']) && is_array($payload['Phones'])) {
            foreach ($payload['Phones'] as $phone) {
                if (!isset($phone['PhoneNumber'])) {
                    continue;
                }
                $identifier = isset($phone['Id']) ? $phone['Id'] : null;
                try {
                    $phoneDef = new IdentityPhoneDefinition($phone['PhoneNumber'], $identifier);
                    if (isset($phone['StatusCode']) && 1 == $phone['StatusCode']) {
                        $phoneDef->getAttributes()->set('isPrimary', true);
                    }
                    if (isset($phone['PhoneContactType']) && isset($typeMap[$phone['PhoneContactType']])) {
                        $phoneDef->getAttributes()->set('phoneType', $typeMap[$phone['PhoneContactType']]);
                    }
                    $definition->addPhone($phoneDef);
                } catch (\Exception $e) {
                }
            }
        }
    }

    /**
     * Validates that the external id is valid.
     *
     * @throws  \InvalidArgumentException   If the identifier is invalid.
     */
    private function validateIdentifier($externalId)
    {
        if (false === $this->isIdentifierValid($externalId)) {
            throw new \InvalidArgumentException(sprintf('The provided identifier `%s` is invalid.', $externalId));
        }
    }
}
