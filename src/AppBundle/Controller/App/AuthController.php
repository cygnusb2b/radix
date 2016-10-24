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
        // @todo This is a temporary solution. Re-evaluate identity detection.
        // $this->detectIdentity($request);
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
        $account  = $this->get('as3_modlr.store')->findQuery('customer-account', $criteria)->getSingleResult();
        if (null === $account) {
            throw new HttpFriendlyException('No account found for the provided token.', 400);
        }

        // Parse/validate.
        $generator = $this->get('app_bundle.customer.password_reset.token_generator');
        $generator->parseFor($token, $account->getId(), []);

        return new JsonResponse(['data' => ['verified' => true, 'primaryEmail' => $account->get('primaryEmail')]], 200);
    }

    /**
     * Detects an identity from an incoming request, if applicable.
     *
     * @todo    This is a temporary solution. Re-evaluate identity detection.
     * @param   Request $request
     */
    private function detectIdentity(Request $request)
    {
        $params = $request->query->get('detect');
        if (!is_array($params) || empty($params)) {
            // No detection params found.
            return;
        }

        if (!isset($params['clientKey']) || (!isset($params['primaryEmail']) && !isset($params['externalId']))) {
            // Required params not found.
            return;
        }

        $manager = $this->get('app_bundle.customer.manager');
        if (true === $manager->isAccountLoggedIn()) {
            // Disallow detection if a user is logged in.
            return;
        }

        try {
            $this->doDetectionFor($params);
        } catch (\Exception $e) {
            // If exception thrown, silently "fail" on the front-end, but track the exception.
            RequestUtility::notifyException($e);
        }
    }

    /**
     * Handles the detection and sets the active identity, if applicable.
     *
     * @todo    This is a temporary solution. Re-evaluate identity detection.
     * @param   array   $payload
     * @return  JsonResponse
     */
    private function doDetectionFor(array $payload)
    {
        // @todo This entire method needs to converted into services. Also, overall integrations need to be re-examined to incorporate detection.
        if ('omeda' !== $payload['clientKey']) {
            throw new HttpFriendlyException('Only detection from Omeda is currently supported.', 400);
        }

        if (!isset($payload['externalId'])) {
            // No support for non-external id.
            return;
        }

        if (0 === preg_match('/^[A-Z0-9]{15}$/', $payload['externalId'])) {
            // No support for non-encrypted id.
            return;
        }

        $manager  = $this->get('app_bundle.customer.manager');
        $store    = $this->get('as3_modlr.store');
        $criteria = [
            'externalIds.source'     => 'omeda',
            'externalIds.identifier' => $payload['externalId'],
        ];

        // Try finding an identity first.
        $identity = $store->findQuery('customer-identity', $criteria)->getSingleResult();

        if (null !== $identity) {
            // Set the active identity.
            // @todo This should upsert the identity data??
            $manager->setActiveIdentity($identity);
            return;
        }


        // Try finding a customer account.
        $account = $store->findQuery('customer-account', $criteria)->getSingleResult();
        if (null === $account) {
            // @todo This would need to create and set a new identity.
            // return $this->createNewIdentity($payload['externalId']);
            return;
        }

        // Try finding an identity by the email address.
        $email = $account->get('primaryEmail');
        if (null !== $email) {
            $identity = $store->findQuery('customer-identity', ['primaryEmail' => $email])->getSingleResult();
            if (null !== $identity) {
                $manager->setActiveIdentity($identity);
                return;
            }

            // No identity found. Create new by cloning the account.
            $cloner   = $this->get('app_bundle.cloning.model_cloner');
            $identity = $store->create('customer-identity');
            $cloner->apply($account, $identity);

            $identity->set('account', $account);
            $identity->set('primaryEmail', $email);
            $identity->save();

            $manager->setActiveIdentity($identity);
            return;
        }

        // @todo This would need to create and set a new identity.
        // return $this->createNewIdentity($payload['externalId']);
    }
}
