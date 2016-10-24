<?php

namespace AppBundle\Factory\Identity;

use AppBundle\Factory\Error;
use AppBundle\Utility\HelperUtility;
use As3\Modlr\Models\AbstractModel;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;

/**
 * Account factory.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class IdentityAccountFactory extends AbstractIdentityFactory
{
    /**
     * @var AccountCredentialsFactory
     */
    private $credentials;

    /**
     * @var AccountEmailFactory
     */
    private $email;

    /**
     * @param   Store                       $store
     * @param   IdentityAddressFactory      $address
     * @param   IdentityPhoneFactory        $phone
     * @param   IdentityAnswerFactory       $answer
     * @param   AccountCredentialsFactory   $credentials
     * @param   AccountEmailFactory         $email
     */
    public function __construct(Store $store, IdentityAddressFactory $address, IdentityPhoneFactory $phone, IdentityAnswerFactory $answer, AccountCredentialsFactory $credentials, AccountEmailFactory $email)
    {
        parent::__construct($store, $address, $phone, $answer);
        $this->credentials = $credentials;
        $this->email       = $email;
    }

    public function apply(Model $identity, array $attributes = [])
    {
        parent::apply($identity, $attributes);
        $this->setPrimaryEmail($identity, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function canSave(AbstractModel $identity)
    {
        if (true !== $result = parent::canSave($identity)) {
            return $result;
        }
        foreach ($identity->get('emails') as $email) {
            if (true !== $result = $this->getEmailFactory()->canSave($email)) {
                // Ensure all emails can be saved.
                return $result;
            }
        }
        return true;
    }

    /**
     * Creates a new account and applies any root attribute data.
     *
     * @param   array   $attributes
     * @return  Model
     */
    public function create(array $attributes = [])
    {
        $identity = parent::create($attributes);

        // Create the initial credentials set.
        $identity->set('credentials', $identity->createEmbedFor('credentials'));

        // Set primary values, if found.
        // @todo Will need to add support for adding additional emails, addresses, phones, etc.
        $this->setPasswordCredential($identity, $attributes);

        // Append the default account settings.
        $this->appendSettings($identity);
        return $identity;
    }

    /**
     * Gets the account credentials factory.
     *
     * @return  AccountCredentialsFactory
     */
    public function getCredentialsFactory()
    {
        return $this->credentials;
    }

    /**
     * Gets the account email factory.
     *
     * @return  AccountEmailFactory
     */
    public function getEmailFactory()
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function getRelatedModelsFor(Model $identity)
    {
        return array_merge(parent::getRelatedModelsFor($identity), $this->getRelatedEmails($identity));
    }

    /**
     * {@inheritdoc}
     */
    public function postValidate(AbstractModel $identity)
    {
        parent::postValidate($identity);
        $credentials = $identity->get('credentials');

        // Ensures the credentials are processed (encode the password, etc).
        $this->getCredentialsFactory()->postValidate($credentials);

        // Set the display name from the user name, if applicable.
        if (null === $identity->get('displayName') && null !== $password = $credentials->get('password')) {
            $username = $password->get('username');
            if (!empty($username)) {
                $identity->set('displayName', $username);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function preValidate(AbstractModel $identity)
    {
        parent::preValidate($identity);
        $this->appendSettings($identity);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Model $model)
    {
        return 'identity-account' === $model->getType();
    }

    /**
     * {@inheritdoc}
     */
    protected function createEmptyInstance()
    {
        return $this->getStore()->create('identity-account');
    }

    /**
     * @param   Model   $identity
     */
    private function appendSettings(Model $identity)
    {
        $settings = $identity->get('settings');
        if (null === $settings) {
            $settings = $identity->createEmbedFor('settings');
            $settings->set('enabled', true);
            $settings->set('locked', false);
            $settings->set('shadowbanned', false);
            $identity->set('settings', $settings);
        }
    }

    /**
     * This is needed in order to ensure newly created emails are also accounted for.
     * Modlr really needs to "automatically" append new inverse models to the owner's collection.
     *
     * @param   Model   $identity
     * @param   Model[]
     */
    private function getRelatedEmails(Model $identity)
    {
        $emails = [];
        foreach ($this->getStore()->getModelCache()->getAllForType('identity-account-email') as $email) {
            if (null === $email->get('account')) {
                continue;
            }
            if ($email->get('account')->getId() === $identity->getId()) {
                $emails[$email->getId()] = $email;
            }
        }
        foreach ($identity->get('emails') as $email) {
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
     * @param   Model   $identity
     * @param   array   $attributes
     */
    private function setPasswordCredential(Model $identity, array $attributes)
    {
        if (!isset($attributes['username']) && !isset($attributes['password'])) {
            return;
        }
        $username = isset($attributes['username']) ? $attributes['username'] : null;
        $password = isset($attributes['password']) ? $attributes['password'] : null;

        // This needs to upsert??? Meaning, if password already set, update and send/set reset code??
        $this->getCredentialsFactory()->applyPasswordCredential(
            $identity->get('credentials'),
            $password,
            'platform',
            $username
        );
    }

    /**
     * Sets the primary email address to the identity model.
     *
     * @todo    Determine how to upsert!!!
     * @param   Model   $identity
     * @param   array   $attributes
     */
    private function setPrimaryEmail(Model $identity, array $attributes)
    {
        if (!isset($attributes['primaryEmail'])) {
            return;
        }
        // @todo This needs to upsert... so, if no primary email found, create new and set.
        $this->getEmailFactory()->create($identity, $attributes['primaryEmail'], true);
    }
}
