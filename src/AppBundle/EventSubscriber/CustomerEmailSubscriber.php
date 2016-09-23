<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Customer\EmailVerifyTokenGenerator;
use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Utility\ModelUtility;
use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Events\ModelLifecycleArguments;

class CustomerEmailSubscriber implements EventSubscriberInterface
{
    /**
     * @var EmailVerifyTokenGenerator
     */
    private $tokenGenerator;

    /**
     * @param   EmailVerifyTokenGenerator   $tokenGenerator
     */
    public function __construct(EmailVerifyTokenGenerator $tokenGenerator)
    {
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * {@inheritDoc}
     */
    public function getEvents()
    {
        return [
            Events::preCommit,
        ];
    }

    /**
     * @param   ModelLifecycleArguments     $args
     */
    public function preCommit(ModelLifecycleArguments $args)
    {
        $model = $args->getModel();
        if (false === $this->shouldProcess($model)) {
            return;
        }

        if (null === $model->get('account')) {
            throw new HttpFriendlyException('All customer email addresses must be assigned to a account.', 400);
        }

        // Format/validate email address.
        $model->set('value', ModelUtility::formatEmailAddress($model->get('value')));

        $this->handleVerification($model);
    }

    /**
     * @param   Model   $model
     * @return  bool
     */
    protected function shouldProcess(Model $model)
    {
        return 'customer-email' === $model->getType();
    }

    /**
     * @param   Model   $model
     */
    private function handleVerification(Model $model)
    {
        if (null === $model->get('verification')) {
            // Ensure a verification object is always set.
            $verification = $model->createEmbedFor('verification');
            $verification->set('verified', false);
            $model->set('verification', $verification);
        }
        if (false === $model->get('verification')->get('verified')) {
            // Attempting to insert a non-verified email address.
            // Check if a verified email address already exists. If so, prevent insert.
            $criteria = ['value' => $model->get('value'), 'verification.verified' => true];
            $email    = $model->getStore()->findQuery('customer-email', $criteria)->getSingleResult();
            if (null !== $email) {
                throw new HttpFriendlyException(sprintf('The email address "%s" is already is use by another account.', $model->get('value')), 400);
            }

            // Generate and set the JWT token for email verification.
            $token = $this->tokenGenerator->createFor(
                $model->get('value'), $model->get('account')->getId()
            );
            $model->get('verification')->set('token', $token);
        }
    }
}
