<?php

namespace AppBundle\Notifications\NotificationFactory\CustomerAccount;

use As3\Modlr\Models\Model;
use AppBundle\Notifications\Notification;
use AppBundle\Notifications\NotificationFactory\CustomerAccount;
use AppBundle\Notifications\NotificationFactoryInterface;

/**
 * Creates a Notification
 *
 * @author Josh Worden <jworden@southcomm.com>
 */
class Activate extends CustomerAccount implements NotificationFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(Model $submission, $actionKey, Model $template = null, array $args)
    {
        if (!isset($args['customer-email']) || null == $args['customer-email']->get('value')) {
            throw new \InvalidArgumentException('Customer email must be present to activate!');
        }

        $args['verificationLink'] = $this->getVerificationLink($args['customer-email'], $submission);
        $app = $args['application'];
        if (null === $template) {
            $args['subject'] = sprintf('Activate your %s account', $app->get('name'));
        }
        return new Notification($args);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Model $submission, $actionKey, Model $template = null)
    {
        return 'customer-account' === $submission->get('sourceKey') && 'activate' === $actionKey;
    }
}
