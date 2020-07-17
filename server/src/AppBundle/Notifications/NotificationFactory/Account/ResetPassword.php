<?php

namespace AppBundle\Notifications\NotificationFactory\Account;

use As3\Modlr\Models\Model;
use AppBundle\Notifications\Notification;
use AppBundle\Notifications\NotificationFactoryInterface;

/**
 * Creates the reset password success notification.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class ResetPassword implements NotificationFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(Model $submission, Model $template = null, array $args)
    {
        $args['subject'] = 'Password Successfully Reset';
        return new Notification($args);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Model $submission, Model $template = null)
    {
        $identity = $submission->get('identity');
        if ('identity-account.reset-password' === $submission->get('sourceKey') && null !== $identity) {
            if ('account-identity' === $identity->getType() && null !== $identity->get('primaryEmail')) {
                return true;
            }
        }
        return false;
    }
}
