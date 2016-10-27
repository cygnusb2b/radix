<?php

namespace AppBundle\Exception;

class EmailVerificationException extends HttpFriendlyException
{
    public function __construct($emailAddress, $accountId)
    {
        parent::__construct('This account is awaiting email verificaton. Please check your email and click the verification link.', 403, [
            'email'     => $emailAddress,
            'account'   => $accountId,
        ]);
    }
}
