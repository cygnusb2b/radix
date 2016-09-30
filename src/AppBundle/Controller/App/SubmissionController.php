<?php

namespace AppBundle\Controller\App;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Utility\ModelUtility;
use AppBundle\Utility\HelperUtility;
use AppBundle\Utility\RequestPayload;
use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SubmissionController extends AbstractAppController
{
    /**
     * Processes a submission and returns the next template result.
     *
     * @todo    Need to determine if the manager should return the result or have the controller do it.
     * @todo    Likely the response handling should be done here. Also need to determine what should happen if there isn't a next step/template.
     * @param   string  $sourceKey
     * @param   Request $request
     * @return  JsonResponse
     */
    public function indexAction($sourceKey, Request $request)
    {
        $manager = $this->get('app_bundle.submission.manager');
        $payload = RequestPayload::createFrom($request);

        return $manager->processFor($sourceKey, $payload);
    }
}
