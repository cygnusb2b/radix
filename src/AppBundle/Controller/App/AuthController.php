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
        $this->detectIdentity($request);
        $manager = $this->get('app_bundle.identity.manager');
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
     * @param   Request $request
     */
    private function detectIdentity(Request $request)
    {
        $params = $request->query->get('identify');
        if (!is_array($params) || empty($params) || !isset($params['identifier'])) {
            // No identification params found.
            return;
        }

        if (!isset($params['pullKey']) && !isset($params['source'])) {
            // Must have either a pull key or a source.
            return;
        }

        if (isset($params['pullKey'])) {
            // Pull identification.
            $manager = $this->get('app_bundle.integration.manager');
            try {
                $identity = $manager->identify($params['pullKey'], $params['identifier']);
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
            // Direct integration
            throw new \BadMethodCallException('Direct identification is not yet supported');
        }
    }
}
