<?php

namespace AppBundle\Identity;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Utility\ModelUtility;
use Lcobucci\JWT\Builder as JWTBuilder;
use Lcobucci\JWT\ValidationData;

/**
 * Generates (and parses) JWT tokens for email verification purposes.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class EmailVerifyTokenGenerator extends AbstractTokenGenerator
{
    /**
     * {@inheritdoc}
     */
    protected function applyParametersToBuilder(JWTBuilder $builder, array $parameters)
    {
        $emailAddress = $this->extractEmailAddressFrom($parameters);
        $builder->setSubject($emailAddress);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyParametersToRules(ValidationData $rules, array $parameters)
    {
        $emailAddress = $this->extractEmailAddressFrom($parameters);
        $rules->setSubject($emailAddress);
    }

    /**
     * Creates an exception on token validation failure.
     *
     * @param   string  $accountId
     * @param   array   $parameters
     * @return  HttpFriendlyException
     */
    protected function createExceptionFor($accountId, array $parameters)
    {
        $emailAddress = $this->extractEmailAddressFrom($parameters);
        return new HttpFriendlyException(sprintf('The verification code for "%s" is either invalid or has expired.', $emailAddress), 403, [
            'email'             => $emailAddress,
            'identity-account'  => $accountId,
        ]);
    }

    /**
     * Extracts the email address from the provided token parameters.
     *
     * @param   array   $parameters
     * @return  string
     * @throws  \InvalidArgumentException
     */
    private function extractEmailAddressFrom(array $parameters)
    {
        $email = isset($parameters['emailAddress']) ? $parameters['emailAddress'] : null;
        $email = ModelUtility::formatEmailAddress($email);
        if (empty($email)) {
            throw new \InvalidArgumentException('No email address found in the token parameters.');
        }
        return $email;
    }
}
