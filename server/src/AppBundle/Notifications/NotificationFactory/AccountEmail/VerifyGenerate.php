<?php

namespace AppBundle\Notifications\NotificationFactory\AccountEmail;

use As3\Modlr\Models\Model;
use AppBundle\Notifications\Notification;
use AppBundle\Notifications\NotificationFactory\AccountTrait;
use AppBundle\Notifications\NotificationFactoryInterface;

/**
 * Creates a Notification
 *
 * @author Josh Worden <jworden@southcomm.com>
 */
class VerifyGenerate implements NotificationFactoryInterface
{
    use AccountTrait;

    /**
     * {@inheritdoc}
     */
    public function generate(Model $submission, Model $template = null, array $args)
    {
        $email = $this->getIdentityEmail($submission, $submission->get('payload')->identity['primaryEmail']);
        $email->reload();
        $args['verificationLink'] = $this->getVerificationLink($submission, $email, $args['application']);
        $args['verificationEmail'] = $email->get('value');
        $args['subject'] = $this->appendFallbackSubject($args, 'Verify your email for %s');
        return new Notification($args);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Model $submission, Model $template = null)
    {
        $identity = $submission->get('identity');
        if ('identity-account-email.verify-generate' === $submission->get('sourceKey') && null !== $identity) {
            if ('identity-account' === $identity->getType() && null !== $identity->get('primaryEmail')) {
                return true;
            }
        }
        return false;
    }
}
