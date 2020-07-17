<?php

namespace AppBundle\Notifications\NotificationFactory\Account;

use As3\Modlr\Models\Model;
use AppBundle\Notifications\Notification;
use AppBundle\Notifications\NotificationFactory\AccountTrait;
use AppBundle\Notifications\NotificationFactoryInterface;

/**
 * Creates the reset password notification.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class ResetPasswordGenerate implements NotificationFactoryInterface
{
    use AccountTrait;

    /**
     * {@inheritdoc}
     */
    public function generate(Model $submission, Model $template = null, array $args)
    {
        $email = $submission->get('identity')->get('primaryEmail');

        $args['resetLink'] = $this->getPasswordResetLink($submission, $submission->get('identity'));
        $args['subject']   = 'Password Reset Request';
        return new Notification($args);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Model $submission, Model $template = null)
    {
        $identity = $submission->get('identity');
        if ('identity-account.reset-password-generate' === $submission->get('sourceKey') && null !== $identity) {
            if ('identity-account' === $identity->getType() && null !== $identity->get('primaryEmail')) {
                return true;
            }
        }
        return false;
    }
}
