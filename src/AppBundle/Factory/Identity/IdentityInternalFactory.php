<?php

namespace AppBundle\Factory\Identity;

use AppBundle\Factory\Error;
use AppBundle\Utility\HelperUtility;
use AppBundle\Utility\ModelUtility;
use As3\Modlr\Models\AbstractModel;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;

/**
 * Internal identity factory.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class IdentityInternalFactory extends AbstractIdentityFactory
{
    /**
     * @var IdentityEmailFactory
     */
    private $email;

    /**
     * @param   Store                   $store
     * @param   IdentityAddressFactory  $address
     * @param   IdentityPhoneFactory    $phone
     * @param   IdentityAnswerFactory   $answer
     * @param   IdentityEmailFactory    $email
     */
    public function __construct(Store $store, IdentityAddressFactory $address, IdentityPhoneFactory $phone, IdentityAnswerFactory $answer, IdentityEmailFactory $email)
    {
        parent::__construct($store, $address, $phone, $answer);
        $this->email = $email;
    }

    /**
     * {@inheritdoc}
     */
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
     * Gets the identity email factory.
     *
     * @return  IdentityEmailFactory
     */
    public function getEmailFactory()
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportsType()
    {
        return 'identity-internal';
    }

    /**
     * Sets the primary email address to the identity model.
     *
     * @todo    Handle when multiple emails are used.
     * @param   Model   $identity
     * @param   array   $attributes
     */
    protected function setPrimaryEmail(Model $identity, array $attributes)
    {
        if (!isset($attributes['primaryEmail'])) {
            return;
        }
        $email = ModelUtility::formatEmailAddress($attributes['primaryEmail']);
        if (empty($email)) {
            return;
        }

        // Force set to primary, since currently this is all the method supports.
        $properties = [
            'value'     => $email,
            'isPrimary' => true,
        ];


        $embedMeta  = $identity->getMetadata()->getEmbed('emails')->embedMeta;
        $factory    = $this->getEmailFactory();

        // @todo Needs to re-vamped when front-end support is added for multiple email addresses.
        // @todo Need to find away to update the specific address
        $primaryEmail = $identity->get('primaryEmail');

        if (true === $identity->getState()->is('new') || null === $primaryEmail) {
            // The identity is new, or no email was previously set. Create and push.
            $email = $factory->create($embedMeta, $properties);
            $identity->pushEmbed('emails', $email);

        } else {
            // The identity is existing, or a primary email already exists. Determine update or create.
            $create = false;
            foreach ($identifier->get('emails') as $email) {
                if ($email->get('value') === $properties['value']) {
                    // Existing email. Update.
                    $create = false;
                    $factory->apply($email, $properties);
                } else {
                    $email->set('isPrimary', false);
                }
            }

            if (true === $create) {
                $email = $factory->create($embedMeta, $properties);
                $identity->pushEmbed('emails', $email);
            }
        }
    }
}
