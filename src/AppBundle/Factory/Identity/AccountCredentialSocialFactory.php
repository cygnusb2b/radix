<?php

namespace AppBundle\Factory\Identity;

use AppBundle\Factory\AbstractEmbedFactory;
use As3\Modlr\Models\AbstractModel;

/**
 * Factory for creating social account credentials.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class AccountCredentialSocialFactory extends AbstractEmbedFactory
{
    /**
     *
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
     * {@inheritodc}
     */
    protected function getSupportsType()
    {
        return 'identity-account-credential-social';
    }
}
