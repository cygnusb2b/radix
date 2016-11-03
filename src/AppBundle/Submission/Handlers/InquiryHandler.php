<?php

namespace AppBundle\Submission\Handlers;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Submission\SubmissionHandlerInterface;
use AppBundle\Utility\HelperUtility;
use AppBundle\Utility\ModelUtility;
use AppBundle\Utility\RequestPayload;
use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\JsonResponse;

class InquiryHandler implements SubmissionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function beforeSave(RequestPayload $payload, Model $submission)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function canSave()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function createResponseFor(Model $submission)
    {
        return new JsonResponse([
            'data' => [
                'template'  => '<h3>Thank you!</h3><p>Your submission has been received.</p>',
            ]
        ], 201);
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceKey()
    {
        return 'inquiry';
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function validateAlways(RequestPayload $payload)
    {
        $meta = $payload->getMeta();
        if (false === $meta->has('model')) {
            throw new HttpFriendlyException('No meta.model member was found in the payload. Unable to process submission.', 422);
        }

        $model = $meta->get('model', []);
        if (!HelperUtility::isSetNotEmpty($model, 'type') || !HelperUtility::isSetNotEmpty($model, 'identifier')) {
            throw new HttpFriendlyException('The inquiry model type and identifier are required', 400);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateWhenLoggedIn(RequestPayload $payload, Model $account)
    {
        $email = $account->get('primaryEmail');
        if (empty($email)) {
            throw new HttpFriendlyException('The email address field is required.', 400);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateWhenLoggedOut(RequestPayload $payload, Model $identity = null)
    {
        $email = ModelUtility::formatEmailAddress($payload->getIdentity()->get('primaryEmail'));
        if (empty($email)) {
            throw new HttpFriendlyException('The email address field is required.', 400);
        }
        if (false === ModelUtility::isEmailAddressValid($email)) {
            throw new HttpFriendlyException('The provided email address is invalid.', 400);
        }
    }
}
