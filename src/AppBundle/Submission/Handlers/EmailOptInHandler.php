<?php

namespace AppBundle\Submission\Handlers;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Factory\ProductEmailDeploymentOptinFactory as OptInFactory;
use AppBundle\Submission\SubmissionHandlerInterface;
use AppBundle\Utility\ModelUtility;
use AppBundle\Utility\RequestPayload;
use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\JsonResponse;

class EmailOptInHandler implements SubmissionHandlerInterface
{
    /**
     * @var OptInFactory
     */
    private $optInFactory;

    /**
     * @var Model[]
     */
    private $optInModels = [];

    /**
     * @param   OptInFactory    $optInFactory
     */
    public function __construct(OptInFactory $optInFactory)
    {
        $this->optInFactory = $optInFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave(RequestPayload $payload, Model $submission)
    {
        $customer = $submission->get('customer');
        if (null !== $customer && 'customer-account' === $customer->getType()) {
            // Logged in user.
            $emailAddress = $customer->get('primaryEmail');
        } else {
            $emailAddress = ModelUtility::formatEmailAddress($payload->getCustomer()->get('primaryEmail'));
        }
        $this->setOptInModelsFor($emailAddress, $payload->getSubmission()->getAsArray('optIns'));
    }

    /**
     * {@inheritdoc}
     */
    public function canSave()
    {
        foreach ($this->optInModels as $optIn) {
            if (true !== $result = $this->optInFactory->canSave($optIn)) {
                $result->throwException();
            }
        }
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
        return 'product-email-deployment-optin';
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        foreach ($this->optInModels as $optIn) {
            $optIn->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateAlways(RequestPayload $payload)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validateWhenLoggedIn(RequestPayload $payload, Model $account)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validateWhenLoggedOut(RequestPayload $payload, Model $identity = null)
    {
        $email = ModelUtility::formatEmailAddress($payload->getCustomer()->get('primaryEmail'));
        if (empty($email)) {
            throw new HttpFriendlyException('The email address field is required.', 400);
        }
        if (false === ModelUtility::isEmailAddressValid($email)) {
            throw new HttpFriendlyException('The provided email address is invalid.', 400);
        }
    }

    /**
     * Sets the Opt-Ins that need saving, based on the submitted opt-in info.
     *
     * @param   string  $emailAddress
     * @param   array   $optIns
     */
    private function setOptInModelsFor($emailAddress, array $optIns)
    {
        $productIds = [];
        foreach ($optIns as $productId => $status) {
            $productIds[] = $productId;
        }

        $store = $this->optInFactory->getStore();

        $this->optInModels = [];
        if (!empty($productIds)) {
            $criteria = ['email' => $emailAddress, 'product' => ['$in' => $productIds]];
            $models   = $store->findQuery('product-email-deployment-optin', $criteria);
            foreach ($models as $model) {
                // Handle updates.
                if (null === $product = $model->get('product')) {
                    continue;
                }

                $model->set('optedIn', $optIns[$product->getId()]);
                $this->optInModels[] = $model;
                unset($optIns[$product->getId()]);
            }

            // Find remaining products and create optins.
            $products = $store->findQuery('product-email-deployment', ['id' => ['$in' => array_keys($optIns)]]);
            foreach ($products as $product) {
                $this->optInModels[] = $this->optInFactory->create($emailAddress, $optIns[$product->getId()], $product);
            }
        }
    }
}
