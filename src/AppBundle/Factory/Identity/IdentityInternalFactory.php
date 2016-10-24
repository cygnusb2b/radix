<?php

namespace AppBundle\Factory\Identity;

use AppBundle\Factory\Error;
use AppBundle\Utility\HelperUtility;
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
        $this->preValidate($identity);

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
    public function supports(Model $model)
    {
        return 'identity-internal' === $model->getType();
    }

    /**
     * {@inheritdoc}
     */
    protected function createEmptyInstance()
    {
        return $this->getStore()->create('identity-internal');
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
        if (false === HelperUtility::isSetArray($attributes, 'primaryEmail')) {
            return;
        }
        if (false === HelperUtility::isSetNotEmpty($attributes['primaryEmail'], 'value')) {
            return;
        }

        $properties = $attributes['primaryEmail'];
        $embedMeta  = $identity->getMetadata()->getEmbed('emails')->embedMeta;
        $factory    = $this->getEmailFactory();

        // @todo Needs to re-vamped when front-end support is added for multiple addresses.
        $primaryEmail = $identity->get('primaryEmail');

        // Force set to primary, since currently this is all the method supports.
        $properties['isPrimary'] = true;

        if (true === $identity->getState()->is('new') || null === $primaryEmail) {
            // The identity is new, or no email was previously set. Create and push.
            $email = $factory->create($embedMeta, $properties);
            $identity->pushEmbed('emails', $email);

        } else {
            // The identity is existing, or a primary email already exists. Determine update or create.
            $create = false;
            if (!isset($properties['identifier'])) {
                // The email is "new" on the front-end. @todo Need to add check to ensure the email value doesn't already exist.
                $create = true;
            } else {
                // Existing email. Attempt to find and update.
                foreach ($identity->get('emails') as $email) {
                    if ($email->get('identifier') === $properties['identifier']) {
                        // Apply the email attributes to the found email.
                        $factory->apply($email, $properties);
                        return;
                    }
                }
                // At this point, the incoming email has an identifier, but it wasn't found on the identity. Treat as a creation.
                $create = true;
            }

            if (true === $create) {
                $email = $factory->create($embedMeta, $properties);
                $identity->pushEmbed('emails', $email);
            }
        }
    }
}
