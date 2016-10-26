<?php

namespace AppBundle\Integrations\Omeda;

use AppBundle\Integration\Handler\IdentifyInterface;

class IdentifyHandler extends AbstractHandler implements IdentifyInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($externalId)
    {
        $this->validateIdentifier($externalId);
        $payload = $this->parseApiResponse($this->getApiClient()->customer->lookup($externalId));
        var_dump(__METHOD__, $payload);
        die();
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
