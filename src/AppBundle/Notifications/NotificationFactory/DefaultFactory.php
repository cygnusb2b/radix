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
    public function generate(Model $submission, $actionKey, Model $template = null, array $args)
    {
        if (!isset($args['subject'])) {
            $app = $args['application'];
            $defaultSubject = sprintf('Notification from %s', $app->get('name'));
            $args['subject'] = $defaultSubject;
        }
        return new Notification($args);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Model $submission, $actionKey, Model $template = null)
    {
        return null !== $template;
    }
}
