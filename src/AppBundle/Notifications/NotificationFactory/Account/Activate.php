<?php

namespace AppBundle\Notifications\NotificationFactory\Account;

use As3\Modlr\Models\Model;
use AppBundle\Notifications\Notification;
use AppBundle\Notifications\NotificationFactory\AccountTrait;
use AppBundle\Notifications\NotificationFactoryInterface;

/**
 * Creates a Notification
 *
 * @author Josh Worden <jworden@southcomm.com>
 */
class Activate implements NotificationFactoryInterface
{
    use AccountTrait;

    /**
     * {@inheritdoc}
     */
    public function generate(Model $submission, Model $template = null, array $args)
    {
        $email = $this->getIdentityEmail($submission, $submission->get('payload')->identity['primaryEmail']);
        $args['verificationLink'] = $this->getVerificationLink($submission, $email, $args['application']);
        $args['subject'] = $this->appendFallbackSubject($args, 'Activate your %s account');
        return new Notification($args);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Model $submission, Model $template = null)
    {
        $identity = $submission->get('identity');
        if ('identity-account' === $submission->get('sourceKey') && null !== $identity) {
            if ('identity-account' === $identity->getType() && null !== $identity->get('primaryEmail')) {
                return true;
            }
        }
        return false;
    }
}
