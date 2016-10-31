<?php

namespace AppBundle\Controller\App;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Utility\RequestUtility;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends AbstractAppController
{
    /**
     * Retrieves the account's auth state.
     *
     * @param   Request $request
     * @return  JsonResponse
     */
    public function retrieveAction(Request $request)
    {
        $manager = $this->get('app_bundle.identity.manager');
        if (false === $manager->isAccountLoggedIn()) {
            // @todo This should be handled by the identify service.
            // The identify action should still run, but no cookies should be set if logged in.
            $this->detectIdentity($request);
        }
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
        $account  = $this->get('as3_modlr.store')->findQuery('identity-account', $criteria)->getSingleResult();
        if (null === $account) {
            throw new HttpFriendlyException('No account found for the provided token.', 400);
        }

        // Parse/validate.
        $generator = $this->get('app_bundle.identity.password_reset.token_generator');
        $generator->parseFor($token, $account->getId(), []);

        return new JsonResponse(['data' => ['verified' => true, 'primaryEmail' => $account->get('primaryEmail')]], 200);
    }

    /**
     * Detects an identity from an incoming request, if applicable.
     *
     * @todo    This should move to a service so it can be handled by auth and the /app/identify endpoint.
     * @param   Request $request
     */
    private function detectIdentity(Request $request)
    {
        $params = $request->query->get('ident');
        if (!is_array($params) || empty($params)) {
            // No identification params found.
            return;
        }

        if (isset($params['pull'])) {
            $parts = explode('|', $params['pull']);
            for ($i=0; $i <= 1; $i++) {
                if (!isset($parts[$i]) || empty($parts[$i])) {
                    return;
                }
            }
            $pullKey    = $parts[0];
            $identifier = $parts[1];

            // Pull identification.
            $manager = $this->get('app_bundle.integration.manager');
            try {
                $identity = $manager->identify($pullKey, $identifier);
                if (null !== $identity) {
                    $this->get('app_bundle.identity.manager')->setActiveIdentity($identity);
                }
            } catch(\Exception $e) {
                if (true === $this->getParameter('kernel.debug')) {
                    throw $e;
                }
                // Catch all internal exceptions.
                RequestUtility::notifyException($e);
            }

        } else {
            throw new \BadMethodCallException('Other forms of identification (e.g. `identifier` and `upsert` are not yet supported.');
        }
    }
}
