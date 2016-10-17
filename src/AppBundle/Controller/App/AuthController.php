<?php

namespace AppBundle\Controller\App;

use AppBundle\Exception\HttpFriendlyException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends AbstractAppController
{
    /**
     * Retrieves the customer account's auth state.
     *
     * @return  JsonResponse
     */
    public function retrieveAction()
    {
        $manager = $this->get('app_bundle.customer.manager');
        return $manager->createAuthResponse();
    }

    /**
     * Verifies a password reset token.
     *
     * @param   string  $token
     * @return  JsonResponse
     * @throws  HttpFriendlyException
     */
    public function verifyResetTokenAction($token)
    {
        if (empty($token)) {
            throw new HttpFriendlyException('No token found in the reset request', 400);
        }

        $criteria = ['credentials.password.resetCode' => $token];
        $account  = $this->get('as3_modlr.store')->findQuery('customer-account', $criteria)->getSingleResult();
        if (null === $account) {
            throw new HttpFriendlyException('No account found for the provided token.', 400);
        }

        // Parse/validate.
        $generator = $this->get('app_bundle.customer.password_reset.token_generator');
        $generator->parseFor($token, $account->getId(), []);

        return new JsonResponse(['data' => ['verified' => true, 'primaryEmail' => $account->get('primaryEmail')]], 200);
    }
}
