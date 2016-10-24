<?php

namespace AppBundle\Notifications\NotificationFactory;

use As3\Modlr\Models\Model;
use AppBundle\Core\AccountManager;
use AppBundle\Utility\ModelUtility;
use Symfony\Component\HttpFoundation\Request;

trait AccountTrait
{
    /**
     * @param   Model   $submission
     * @param   Model   $account
     */
    protected function getPasswordResetLink(Model $submission, Model $account)
    {
        return sprintf(
            '%s?radix.action=ResetPassword&radix.token=%s',
            $submission->get('referringHost'),
            $account->get('credentials')->get('password')->get('resetCode')
        );
    }

    /**
     * @param   Model   $identityEmail
     * @param   Model   $submission
     */
    protected function getVerificationLink(Model $submission, Model $identityEmail, Model $application)
    {
        return sprintf(
            '%s?radix.action=VerifyEmail&radix.token=%s',
            $submission->get('referringHost'),
            $identityEmail->get('verification')->get('token')
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
     * Returns the active identity email model
     *
     * @param   Model   $submission
     * @return  Model   $email
     */
    private function getIdentityEmail(Model $submission, $value)
    {
        foreach ($submission->get('identity')->get('emails') as $email) {
            if ($value === $email->get('value')) {
                return $email;
            }
        }
        throw new \RuntimeException(sprintf('Could not find email for identity %s', $submission->get('identity')->getId()));
    }
}
