<?php

namespace AppBundle\Factory\Identity;

use AppBundle\Factory\AbstractEmbedFactory;
use AppBundle\Factory\Error;
use As3\Modlr\Models\AbstractModel;
use As3\Modlr\Models\Embed;
use As3\Modlr\Models\Model;

/**
 * Factory for identity account credentials
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class AccountCredentialsFactory extends AbstractEmbedFactory
{
    /**
     * @var AccountCredentialPasswordFactory
     */
    private $password;

    /**
     * @var AccountCredentialSocialFactory
     */
    private $social;

    /**
     * @param   AccountCredentialPasswordFactory   $password
     * @param   AccountCredentialSocialFactory     $social
     */
    public function __construct(AccountCredentialPasswordFactory $password, AccountCredentialSocialFactory $social)
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

    /**
     * {@interitdoc}
     */
    public function canSave(AbstractModel $credentials)
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

    /**
     * @return  AccountCredentialPasswordFactory
     */
    public function getPasswordFactory()
    {
        return $this->password;
    }

    /**
     * @return  AccountCredentialSocialFactory
     */
    public function getSocialFactory()
    {
        return $this->social;
    }

    /**
     * {@interitdoc}
     */
    public function postValidate(AbstractModel $credentials)
    {
        if (null !== $password = $credentials->get('password')) {
            $this->getPasswordFactory()->postValidate($password);
        }
    }

    /**
     * {@interitdoc}
     */
    public function preValidate(AbstractModel $credentials)
    {
        if (null !== $password = $credentials->get('password')) {
            $this->getPasswordFactory()->preValidate($password);
        }
    }

    /**
     * {@inheritodc}
     */
    protected function getSupportsType()
    {
        return 'identity-account-credentials';
    }
}
