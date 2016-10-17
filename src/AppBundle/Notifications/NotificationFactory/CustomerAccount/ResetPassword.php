<?php

namespace AppBundle\Notifications\NotificationFactory\CustomerAccount;

use As3\Modlr\Models\Model;
use AppBundle\Notifications\Notification;
use AppBundle\Notifications\NotificationFactory\CustomerTrait;
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
        $customer = $submission->get('customer');
        if ('customer-account.reset-password' === $submission->get('sourceKey') && null !== $customer) {
            if ('customer-identity' !== $customer->getType() && null !== $customer->get('primaryEmail')) {
                return true;
            }
        }
        return false;
    }
}
