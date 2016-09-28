<?php

namespace AppBundle\Factory\Customer;

use AppBundle\Factory\AbstractModelFactory;
use AppBundle\Factory\Error;
use AppBundle\Factory\ValidationFactoryInterface;
use As3\Modlr\Models\AbstractModel;
use As3\Modlr\Models\Model;

/**
 * Factory for creating customer credentials.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class CustomerCredentialsSocialFactory extends AbstractModelFactory implements ValidationFactoryInterface
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
     * {@inheritdoc}
     */
    public function canSave(AbstractModel $credentials)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function postValidate(AbstractModel $credential)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function preValidate(AbstractModel $credential)
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
