<?php

namespace AppBundle\Factory;

use As3\Modlr\Models\Model;
use AppBundle\Utility\HelperUtility;

/**
 * Customer factory for creating/updating/upserting customer accounts.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class CustomerAccountFactory extends AbstractCustomerFactory
{
    /**
     * @var CustomerEmailFactory
     */
    private $email;

    /**
     * @var CustomerCredentialsFactory
     */
    private $credentials;

    /**
     * Constructor.
     */
    public function __construct(CustomerAddressFactory $address, CustomerPhoneFactory $phone, CustomerAnswerFactory $answer, CustomerCredentialsFactory $credentials, CustomerEmailFactory $email)
    {
        parent::__construct($address, $phone, $answer);
        $this->credentials = $credentials;
        $this->email       = $email;
    }

    /**
     * Applies password credentials to the provided customer account.
     *
     * @param   Model       $account
     * @param   string      $clearPassword  The cleartext (unencoded) password.
     * @param   string      $mechanism
     * @param   string|null $username
     * @return  Model
     */
    public function applyPasswordCredential(Model $account, $clearPassword, $mechanism = 'platform', $username = null)
    {
        $credentials = $account->get('credentials') ?: $account->createEmbedFor('credentials');

        $this->getCredentialsFactory()->applyPasswordCredential($credentials, $clearPassword, $mechanism, $username);

        $customer->set('credentials', $credentials);
        return $account;
    }

    public function canSave(Model $customer)
    {
        if (true !== $result = parent::canSave($customer)) {
            return $result;
        }

        $this->preValidate($customer);
        $credentials = $customer->get('credentials');
        if (null === $credentials) {
            return new Error('Customer accounts must define at least one set of credentials.');
        }

        if (null === $credentials->get('password') && 0 === count($credentials->get('social'))) {
            return new Error('Customer accounts must define at least one set of credentials (password or social).');
        }

        if (true !== $result = $this->getCredentialsFactory()->canSave($credentials)) {
            // Ensure the credentials can be saved.
            return $result;
        }

        foreach ($this->getRelatedEmails($customer) as $email) {
            if (true !== $result = $this->getEmailFactory()->canSave($email)) {
                // Ensure all emails can be saved.
                return $result;
            }
        }
        return true;
    }

    /**
     * Creates a new customer and applies any root attribute data.
     *
     * @param   array   $attributes
     * @return  Model
     */
    public function create(array $attributes = [])
    {
        $customer = parent::create($attributes);

        // Create the initial credentials set.
        $customer->set('credentials', $customer->createEmbedFor('credentials'));

        // Set primary values, if found.
        // @todo Will need to add support for adding additional emails, addresses, phones, etc.
        $this->setPrimaryEmail($customer, $attributes);
        $this->setPasswordCredential($customer, $attributes);

        // Append the default account settings.
        $this->appendSettings($customer);
        return $customer;
    }

    public function getRelatedModelsFor(Model $customer)
    {
        return array_merge(parent::getRelatedModelsFor($customer), $this->getRelatedEmails($customer));
    }

    /**
     * Actions that always run (during save) before validation occurs.
     *
     * @param   Model   $customer
     */
    public function preValidate(Model $customer)
    {
        $this->appendSettings($customer);
    }

    /**
     * Actions that always run (during save) after validation occurs.
     *
     * @param   Model   $customer
     */
    public function postValidate(Model $customer)
    {
        $credentials = $customer->get('credentials');

        // Ensures the credentials are processed (encode the password, etc).
        $this->getCredentialsFactory()->postValidate($credentials);

        // Set the display name from the user name, if applicable.
        if (null === $customer->get('displayName') && null !== $password = $credentials->get('password')) {
            $username = $password->get('username');
            if (!empty($username)) {
                $customer->set('displayName', $username);
            }
        }
    }

    /**
     * Gets the customer credentials factory.
     *
     * @return  CustomerCredentialsPasswordFactory
     */
    public function getCredentialsFactory()
    {
        $this->credentials->setStore($this->getStore());
        return $this->credentials;
    }

    /**
     * Gets the customer email factory.
     *
     * @return  CustomerCredentialsPasswordFactory
     */
    public function getEmailFactory()
    {
        $this->email->setStore($this->getStore());
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    protected function createEmptyInstance()
    {
        return $this->getStore()->create('customer-account');
    }

    /**
     * @param   Model   $customer
     */
    private function appendSettings(Model $customer)
    {
        $settings = $customer->get('settings');
        if (null === $settings) {
            $settings = $customer->createEmbedFor('settings');
            $settings->set('enabled', true);
            $settings->set('locked', false);
            $settings->set('shadowbanned', false);
            $customer->set('settings', $settings);
        }
    }

    /**
     * This is needed in order to ensure newly created emails are also accounted for.
     * Modlr really needs to "automatically" append new inverse models to the owner's collection.
     *
     * @param   Model   $customer
     * @param   Model[]
     */
    private function getRelatedEmails(Model $customer)
    {
        $emails = [];
        foreach ($this->getStore()->getModelCache()->getAllForType('customer-email') as $email) {
            if (null === $email->get('customer')) {
                continue;
            }
            if ($email->get('account')->getId() === $customer->getId()) {
                $emails[$email->getId()] = $email;
            }
        }
        foreach ($customer->get('emails') as $email) {
            if (!isset($emails[$email->getId()])) {
                $emails[$email->getId()] = $email;
            }
        }
        return $emails;
    }

    /**
     * Sets the username/password credential.
     *
     * @todo    Determine how to upsert!!!
     * @param   Model   $customer
     * @param   array   $attributes
     */
    private function setPasswordCredential(Model $customer, array $attributes)
    {
        if (!isset($attributes['username']) && !isset($attributes['password'])) {
            return;
        }
        $username = isset($attributes['username']) ? $attributes['username'] : null;
        $password = isset($attributes['password']) ? $attributes['password'] : null;

        // This needs to upsert??? Meaning, if password already set, update and send/set reset code??
        $this->getCredentialsFactory()->applyPasswordCredential(
            $customer->get('credentials'),
            $password,
            'platform',
            $username
        );
    }

    /**
     * Sets the primary email address to the custoemr model.
     *
     * @todo    Determine how to upsert!!!
     * @param   Model   $customer
     * @param   array   $attributes
     */
    private function setPrimaryEmail(Model $customer, array $attributes)
    {
        if (!isset($attributes['primaryEmail'])) {
            return;
        }
        // @todo This needs to upsert... so, if no primary email found, create new and set.
        $this->getEmailFactory()->create($customer, $attributes['primaryEmail'], true);
    }
}
