<?php

namespace AppBundle\Identity;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Utility\ModelUtility;
use Lcobucci\JWT\Builder as JWTBuilder;
use Lcobucci\JWT\ValidationData;

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
    protected function applyParametersToBuilder(JWTBuilder $builder, array $parameters)
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function applyParametersToRules(ValidationData $rules, array $parameters)
    {
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
        return new HttpFriendlyException('The password reset code is either invalid or has expired.', 403, [
            'identity-account' => $accountId,
        ]);
    }
}
