<?php

namespace AppBundle\Notifications\NotificationFactory;

use As3\Modlr\Models\Model;
use AppBundle\Utility\ModelUtility;
use Symfony\Component\HttpFoundation\Request;

trait CustomerTrait
{
    /**
     * @param   Model   $customerEmail
     * @param   Model   $submission
     */
    protected function getVerificationLink(Model $customerEmail)
    {
        $request = Request::createFromGlobals();
        return sprintf(
            '%s/app/submission/customer-email.verify-submit?token=%s',
            $request->getSchemeAndHttpHost(),
            $customerEmail->get('verification')->get('token')
        );
    }

    /**
     * Appends fallback subject to template arguments
     *
     * @param   array   $args
     * @return  string  The fallback subject line
     */
    protected function appendFallbackSubject(array $args, $subject = 'Notification from %s')
    {
        if (isset($args['subject'])) {
            return $args['subject'];
        }
        $appName = $args['application']->get('name');
        if (null !== $name = ModelUtility::getModelValueFor($args['application'], 'settings.branding.name')) {
            $appName = $name;
        }
        return sprintf($subject, $appName);
    }

    /**
     * Returns the active `customer-email` model
     *
     * @param   Model   $submission
     * @return  Model   $email
     */
    private function getCustomerEmail(Model $submission)
    {
        foreach ($submission->get('customer')->get('emails') as $email) {
            return $email;
        }
    }
}
