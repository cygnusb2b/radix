<?php

namespace AppBundle\Notifications\NotificationFactory;

use As3\Modlr\Models\Model;
use As3\Parameters\Parameters;
use AppBundle\Notifications\Notification;
use AppBundle\Notifications\NotificationFactory;
use AppBundle\Notifications\NotificationFactoryInterface;

/**
 * Creates a Notification
 *
 * @author Josh Worden <jworden@southcomm.com>
 */
class DefaultFactory implements NotificationFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(Model $submission, Model $template = null, array $args)
    {
        if (!isset($args['subject'])) {
            $app = $args['application'];
            $defaultSubject = sprintf('Notification from %s', $app->get('name'));
            $args['subject'] = $defaultSubject;
        }
        return new Notification($args);
    }

    /**
     * A database-level template MUST be defined for custom notifications to send.
     *
     * {@inheritdoc}
     */
    public function supports(Model $submission, Model $template = null)
    {
        $customer = $submission->get('customer');
        return null !== $template && null !== $customer && null !== $customer->get('primaryEmail');
    }
}
