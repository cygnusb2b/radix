<?php

namespace AppBundle\Factory;

use As3\Modlr\Models\Embed;
use As3\Modlr\Models\Model;

/**
 * Factory for customer credentials
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class CustomerCredentialsFactory extends AbstractModelFactory
{
    private $password;

    private $social;

    public function __construct(CustomerCredentialsPasswordFactory $password, CustomerCredentialsSocialFactory $social)
    {
        $this->password = $password;
        $this->social   = $social;
    }

    /**
     * Applies credential details to the password credentials models.
     *
     * @param   Embed       $credentials
     * @param   string      $clearPassword  The cleartext (unencoded) password.
     * @param   string      $mechanism
     * @param   string|null $username
     * @return  Embed
     */
    public function applyPasswordCredential(Embed $credentials, $clearPassword, $mechanism = 'platform', $username = null)
    {
        if (false === $this->supportsEmbed($credentials)) {
            $this->getUnsupportedError()->throwException();
        }

        $credential = $credentials->createEmbedFor('password');
        $this->getPasswordFactory()->apply($credential, $clearPassword, $mechanism, $username);
        $credentials->set('password', $credential);
        return $credentials;
    }

    public function getPasswordFactory()
    {
        $this->password->setStore($this->getStore());
        return $this->password;
    }

    public function getSocialFactory()
    {
        $this->social->setStore($this->getStore());
        return $this->social;
    }

    public function preValidate(Embed $credentials)
    {
    }

    public function canSave(Embed $credentials)
    {
        if (false === $this->supportsEmbed($credentials)) {
            // Ensure this is the correct embed model.
            return $this->getUnsupportedError();
        }

        $this->preValidate($credentials);

        if (null !== $password = $credentials->get('password')) {
            // Ensure password credential can be saved.
            if (true !== $result = $this->getPasswordFactory()->canSave($password)) {
                return $result;
            }
        }
        return true;
    }

    public function postValidate(Embed $credentials)
    {
        if (null !== $password = $credentials->get('password')) {
            $this->getPasswordFactory()->postValidate($password);
        }
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
     * @param   Embed   $password
     * @return  bool
     */
    private function supportsEmbed(Embed $password)
    {
        return 'customer-credentials' === $password->getName();
    }
}
