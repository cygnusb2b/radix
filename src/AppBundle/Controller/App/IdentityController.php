<?php

namespace AppBundle\Controller\App;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Utility\RequestUtility;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentityController extends AbstractAppController
{
    /**
     * Handles an identity detection request.
     *
     * @param   Request $request
     * @return  JsonResponse
     */
    public function detectAction(Request $request)
    {
        $manager = $this->get('app_bundle.customer.manager');
        if (true === $manager->isAccountLoggedIn()) {
            // Disallow detection if a user is logged in.
            // @todo Should the detection still load/import the user... just not set an identity value?? Probably.
            return $this->createDefaultResponse();
        }
        $payload = RequestUtility::extractPayload($request);
        $this->validatePayload($payload);
        try {
            return $this->doDetectionFor($payload);
        } catch (\Exception $e) {
            // If exception thrown, silently "fail" on the front-end, but track the exception.
            RequestUtility::notifyException($e);
            return $this->createDefaultResponse();
        }

    }

    /**
     * Creates the default detection response.
     *
     * @return  JsonResponse
     */
    private function createDefaultResponse()
    {
        return new JsonResponse(['data' => ['identity' => null]], 200);
    }

    /**
     * Handles the detection and returns the detected response, if applicable.
     *
     * @param   array   $payload
     * @return  JsonResponse
     */
    private function doDetectionFor(array $payload)
    {
        return $this->createDefaultResponse();
    }

    /**
     * Validates the payload for all detection requests.
     *
     * @param   array   $payload
     * @throws  HttpFriendlyException
     */
    private function validatePayload(array $payload)
    {
        if (!isset($payload['clientKey']) || (!isset($payload['primaryEmail']) && !isset($payload['externalId']))) {
            throw new HttpFriendlyException('Invalid identity detection payload provided. Unable to process.', 422);
        }
    }
}
