<?php

namespace AppBundle\Notifications\NotificationFactory;

use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\Request;

abstract class CustomerAccount
{
    /**
     * @param   Model   $customerEmail
     * @param   Model   $submission
     */
    protected function getVerificationLink(Model $customerEmail, Model $submission)
    {
        $host = $submission->get('referringHost') ?: $this->getDefaultHost();
        return sprintf(
            '%s/verify-email?token=%s',
            $host,
            $customerEmail->get('verification')->get('token')
        );
    }

    /**
     * As a last resort, use the current request to determine the host
     *
     * @return  string
     */
    private function getDefaultHost()
    {
        $request = Request::createFromGlobals();
        return $request->getSchemeAndHost();
    }
}
