<?php

namespace AppBundle\Factory\Customer;

use AppBundle\Customer\EmailVerifyTokenGenerator;
use AppBundle\Factory\AbstractModelFactory;
use AppBundle\Factory\Error;
use AppBundle\Factory\SubscriberFactoryInterface;
use AppBundle\Utility\ModelUtility;
use As3\Modlr\Models\AbstractModel;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;

/**
 * Factory for customer email models.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class CustomerEmailFactory extends AbstractModelFactory implements SubscriberFactoryInterface
{
    /**
     * @todo Eventually this should be set by an auth schema mechanism.
     */
    const REQUIRE_EMAIL = true;

    /**
     * @var EmailVerifyTokenGenerator
     */
    private $tokenGenerator;

    /**
     * @param   Store                       $store
     * @param   EmailVerifyTokenGenerator   $tokenGenerator
     */
    public function __construct(Store $store, EmailVerifyTokenGenerator $tokenGenerator)
    {
        parent::__construct($store);
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function canSave(AbstractModel $email)
    {
        $this->preValidate($email);
        if (null === $email->get('account')) {
            // Ensure a customer account has been assigned.
            return new Error('All customer email addresses must be assigned to a account.');
        }

        $value = $email->get('value');
        if (true === $this->requiresEmail() && empty($value)) {
            // Ensure email address is set.
            return new Error('The email address field is required.', 400);
        }

        if (!empty($value) && false === ModelUtility::isEmailAddressValid($value)) {
            // Ensure email address is valid format.
            return new Error(sprintf('The provided email address `%s` is invalid.', $value), 400);
        }

        if (false === $email->get('verification')->get('verified')) {
            if (null !== $this->retrieveCustomerViaEmailAddress($value)) {
                return new Error(sprintf('The email address `%s` is already in use by another account.', $value), 400);
            }
        }
        return true;
    }

    /**
     * Creates a new customer account email for the provided customer account.
     *
     * @param   Model   $account
     * @param   array   $rootAttributes
     * @return  Model
     */
    public function create(Model $account, $value, $isPrimary = false)
    {
        $email = $this->getStore()->create('customer-email');

        $email->set('value', $value);
        $email->set('isPrimary', $isPrimary);
        $email->set('account', $account);

        $this->setDefaultVerification($email);
        return $email;
    }

    /**
     * {@inheritdoc}
     */
    public function postSave(Model $model)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postValidate(AbstractModel $email)
    {
        // Generate and set the JWT token for non-verified emails.
        $verification = $email->get('verification');
        if (false === $verification->get('verified')) {
            $token = $this->tokenGenerator->createFor(
                $email->get('value'), $email->get('account')->getId()
            );
            $verification->set('token', $token);
        }

        // Append the display name to the customer account, when applicable.
        $displayName = $email->get('account')->get('displayName');
        if (true === $email->getState()->is('new') && empty($displayName)) {
            preg_match('/^(.+)@/i', $email->get('value'), $matches);
            if (isset($matches[1])) {
                $account = $email->get('account');
                $account->set('displayName', $matches[1]);
                $account->save();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function preValidate(AbstractModel $email)
    {
        $this->formatEmailAddress($email);

        if (null === $email->get('verification')) {
            // Ensure a verification object is always set.
            $this->setDefaultVerification($email);
        }
    }

    /**
     * Retrieves a customer account based on a verified email address.
     *
     * @param   string  $emailAddress
     * @return  Model|null
     */
    public function retrieveCustomerViaEmailAddress($emailAddress)
    {
        // Try email address
        $criteria = [
            'value'    => strtolower($emailAddress),
            'verification.verified' => true,
        ];
        $email = $this->getStore()->findQuery('customer-email', $criteria)->getSingleResult();
        if (null !== $email && null !== $email->get('account') && false === $email->get('account')->get('deleted')) {
            // Valid customer.
            return $email->get('account');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Model $model)
    {
        return 'customer-email' === $model->getType();
    }

    /**
     * Formats the email address value for the provided email model.
     *
     * @param   Model   $email
     */
    private function formatEmailAddress(Model $email)
    {
        $value = ModelUtility::formatEmailAddress($email->get('value'));
        $value = (empty($value)) ? null : $value;
        $email->set('value', $value);
    }

    /**
     * Determines if an email address is required.
     *
     * @return  bool
     */
    private function requiresEmail()
    {
        return self::REQUIRE_EMAIL;
    }

    /**
     * @param   Model   $email
     */
    private function setDefaultVerification(Model $email)
    {
        $verification = $email->createEmbedFor('verification');
        $verification->set('verified', false);
        $email->set('verification', $verification);
    }
}
