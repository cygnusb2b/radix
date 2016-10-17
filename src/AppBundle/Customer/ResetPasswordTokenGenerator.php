<?php

namespace AppBundle\Customer;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Utility\ModelUtility;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Token as JWTToken;

/**
 * Generates (and parses) JWT tokens for password reset purposes.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class ResetPasswordTokenGenerator extends AbstractTokenGenerator
{
    /**
     * {@inheritdoc}
     */
    protected function applyParametersToRules(ValidationData $rules, array $parameters)
    {
        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    protected function applyParametersToToken(JWTToken $token, array $parameters)
    {
        return $token;
    }

    /**
     * Creates an exception on token validation failure.
     *
     * @param   string  $customerId
     * @param   array   $parameters
     * @return  HttpFriendlyException
     */
    protected function createExceptionFor($customerId, array $parameters)
    {
        return new HttpFriendlyException('The password reset code is either invalid or has expired.', 403, [
            'customer'  => $customerId,
        ]);
    }
}
