<?php

namespace AppBundle\Factory;

use AppBundle\Security\User\Customer;
use As3\Modlr\Models\Embed;
use As3\Modlr\Models\Model;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

/**
 * Factory for creating customer credentials.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class CustomerCredentialsPasswordFactory extends AbstractModelFactory
{
    /**
     * @todo Eventually this should be set by an auth schema mechanism.
     */
    const REQUIRE_USERNAME = false;
    const USERNAME_MIN     = '4';

    /**
     * @var UserPasswordEncoder
     */
    private $encoder;

    /**
     * @var array
     */
    private $mechanisms = [
        'platform'  => true,
        'merrick'   => true,
    ];

    /**
     * @param   UserPasswordEncoder $encoder
     */
    public function __construct(UserPasswordEncoder $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * Applys values to a customer password credential.
     *
     * @param   Embed       $credential
     * @param   string      $clearPassword  The cleartext (unencoded) password.
     * @param   string      $mechanism
     * @param   string|null $username
     * @return  Embed
     */
    public function apply(Embed $credential, $clearPassword, $mechanism = 'platform', $username = null, $salt = null)
    {
        if (false === $this->supportsEmbed($credential)) {
            $this->getUnsupportedError()->throwException();
        }

        $username = (empty($username)) ? null : $username;
        $credential->set('username', $username);
        $credential->set('value', $clearPassword);
        $credential->set('mechanism', $mechanism);
        $credential->set('salt', $salt);

        if ('platform' === $mechanism) {
            // Do not allow salts for platform accounts since bcrypt is used.
            $credential->set('salt', null);
        }
        return $credential;
    }

    public function preValidate(Embed $credential)
    {
    }

    /**
     * Determines if the credential embed can be saved.
     *
     * @param   Embed   $credential
     * @return  true|Error
     */
    public function canSave(Embed $credential)
    {
        if (false === $this->supportsEmbed($credential)) {
            // Ensure this is the correct embed model.
            return $this->getUnsupportedError();
        }

        $this->preValidate($credential);

        $mechanism = $credential->get('mechanism');
        if (!isset($this->mechanisms[$mechanism])) {
            // Ensure mechanism is supported.
            return new Error('The provided password mechanism is not supported.');
        }

        $value = $credential->get('value');
        if (strlen($value) < 4 || strlen($value) > 72) {
            // Ensure password is the required length.
            return new Error('The password must be between 4 and 72 characters.', 400);
        }

        $username = $credential->get('username');
        if (true === $this->requiresUsername() && strlen($username) < self::USERNAME_MIN) {
            // Ensure username is minimum length.
            return new Error(sprintf('The username must be set an be at least %s characters long.', self::USERNAME_MIN), 400);
        }

        if (!empty($username)) {
            if (false !== stripos($username, '@')) {
                return new Error('Usernames cannot contain the @ symbol.', 400);
            }
            // Ensure username isn't already in use.
            if (null !== $this->retrieveCustomerViaUsername($username)) {
                return new Error(sprintf('The username "%s" is already associated with an account. Please choose another.', $username), 400);
            }
        }
        return true;
    }

    public function postValidate(Embed $credential)
    {
        // Encode password.
        $encoded = $this->encoder->encodePassword(new Customer($this->getStore()->create('customer-account')), $credential->get('value'));
        $credential->set('value', $encoded);
    }

    /**
     * Retrieves a customer account based on a username.
     *
     * @param   string  $username
     * @return  Model|null
     */
    public function retrieveCustomerViaUsername($username)
    {
        $criteria = [
            'credentials.password.username' => $username,
        ];
        $customer = $this->getStore()->findQuery('customer-account', $criteria)->getSingleResult();
        if (null !== $customer && false === $customer->get('deleted')) {
            return $customer;
        }
    }

    /**
     * Gets the unsupported embed type error.
     *
     * @return  Error
     */
    private function getUnsupportedError()
    {
        return new Error('The provided embed model is not supported. Expected an instance of `customer-credential-password`');
    }

    /**
     * Determines if a username is required on the credentials.
     *
     * @return  bool
     */
    private function requiresUsername()
    {
        return self::REQUIRE_USERNAME;
    }

    /**
     * Determines if the embed is supported.
     *
     * @param   Embed   $password
     * @return  bool
     */
    private function supportsEmbed(Embed $password)
    {
        return 'customer-credential-password' === $password->getName();
    }
}
