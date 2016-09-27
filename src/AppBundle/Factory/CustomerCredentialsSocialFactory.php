<?php

namespace AppBundle\Factory;

use As3\Modlr\Models\Model;

/**
 * Factory for creating customer credentials.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class CustomerCredentialsSocialFactory extends AbstractModelFactory
{
    /**
     * Applys a password credential to a customer credentials model.
     *
     * @param   Embed       $credentials
     * @param   string      $clearPassword  The cleartext (unencoded) password.
     * @param   string      $mechanism
     * @param   string|null $username
     * @return  Embed
     */
    public function apply()
    {
    }

    /**
     * Determines if the credentials embed can be saved.
     *
     * @param   Embed   $credentials
     * @return  true|Error
     */
    public function canSave(Embed $credentials)
    {

    }

    /**
     * Gets the unsupported embed type error.
     *
     * @return  Error
     */
    private function getUnsupportedError()
    {
        return new Error('The provided embed model is not supported. Expected an instance of `customer-credentials`');
    }

    /**
     * Determines if the embed is supported.
     *
     * @param   Embed   $credentials
     * @return  bool
     */
    private function supportsEmbed(Embed $credentials)
    {
        return 'customer-credentials' === $credentials->getName();
    }
}
