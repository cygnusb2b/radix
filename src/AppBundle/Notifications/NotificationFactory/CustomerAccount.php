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
    protected function getVerificationLink(Model $customerEmail)
    {
        $request = Request::createFromGlobals();
        return sprintf(
            '%s/app/submission/customer-email.verify-submit?submission:token=%s',
            $request->getSchemeAndHttpHost(),
            $customerEmail->get('verification')->get('token')
        );
    }
}