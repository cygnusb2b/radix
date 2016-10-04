<?php

namespace AppBundle\Notifications\NotificationFactory\CustomerEmail;

use As3\Modlr\Models\Model;
use AppBundle\Notifications\Notification;
use AppBundle\Notifications\NotificationFactory\CustomerTrait;
use AppBundle\Notifications\NotificationFactoryInterface;

/**
 * Creates a Notification
 *
 * @author Josh Worden <jworden@southcomm.com>
 */
class VerifyGenerate implements NotificationFactoryInterface
{
    use CustomerTrait;

    /**
     * {@inheritdoc}
     */
    public function generate(Model $submission, Model $template = null, array $args)
    {
        $email = $this->getCustomerEmail($submission, $submission->get('payload')->customer['primaryEmail']);
        $args['verificationLink'] = $this->getVerificationLink($email, $args['application']);
        $args['verificationEmail'] = $email->get('value');
        $args['subject'] = $this->appendFallbackSubject($args, 'Verify your email for %s');
        return new Notification($args);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Model $submission, Model $template = null)
    {
        $customer = $submission->get('customer');
        if ('customer-email.verify-generate' === $submission->get('sourceKey') && null !== $customer) {
            if ('customer-identity' !== $customer->getType() && null !== $customer->get('primaryEmail')) {
                return true;
            }
        }
        return false;
    }
}
