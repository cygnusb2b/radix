<?php

namespace AppBundle\Factory\Identity;

use AppBundle\Factory\AbstractEmbedFactory;
use AppBundle\Factory\Error;
use AppBundle\Utility\ModelUtility;
use As3\Modlr\Models\AbstractModel;
use As3\Modlr\Models\Embed;

/**
 * Factory for identity emails.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class IdentityEmailFactory extends AbstractEmbedFactory
{
    /**
     * @var string[]
     */
    private $types = ['Personal', 'Business', 'Other'];

    /**
     * {@inheritdoc}
     */
    public function canSave(AbstractModel $email)
    {
        if (false === $this->supportsEmbed($email)) {
            return $this->getUnsupportedError();
        }
        $this->preValidate($email);

        $value = $email->get('value');
        if (empty($value)) {
            // All embedded email addresses required a value.
            // If an outside form our source does NOT require an email address, measures should be taken to ensure the embedded email model is never created.
            return new Error('The email address value is required.', 400);
        }
        if (false === ModelUtility::isEmailAddressValid($value)) {
            // Ensure email address is valid format.
            return new Error(sprintf('The provided email address `%s` is invalid.', $value), 400);
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function postValidate(AbstractModel $email)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function preValidate(AbstractModel $email)
    {
        // Format the email address.
        $value = ModelUtility::formatEmailAddress($email->get('value'));
        $value = (empty($value)) ? null : $value;
        $email->set('value', $value);
    }

    /**
     * {@inheritodc}
     */
    protected function getSupportsType()
    {
        return 'identity-email';
    }
}
