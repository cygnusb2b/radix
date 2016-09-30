<?php

namespace AppBundle\Factory;

use AppBundle\Utility\ModelUtility;
use As3\Modlr\Models\AbstractModel;
use As3\Modlr\Models\Model;

/**
 * Factory for email deployment optin models.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class ProductEmailDeploymentOptinFactory extends AbstractModelFactory implements SubscriberFactoryInterface
{
    /**
     * Applies the required fields to the optin model.
     *
     * @param   Model       $optin
     * @param   string      $emailAddress
     * @param   bool        $optedIn
     * @param   Model|null  $product
     * @return  Model
     */
    public function apply(Model $optin, $emailAddress, $optedIn = false, Model $product = null)
    {
        $optin->set('email', $emailAddress);
        $optin->set('optedIn', $optedIn);
        $optin->set('product', $product);
        return $optin;
    }

    /**
     * {@inheritdoc}
     */
    public function canSave(AbstractModel $optin)
    {
        $this->preValidate($optin);
        if (null === $optin->get('product')) {
            return new Error('All email deployment opt-ins must be related to an email product.');
        }

        $value = $optin->get('email');
        if (empty($value)) {
            // Ensure email address is set.
            return new Error('The email address field is required.', 400);
        }

        if (false === ModelUtility::isEmailAddressValid($value)) {
            // Ensure email address is valid format.
            return new Error(sprintf('The provided email address `%s` is invalid.', $value), 400);
        }
        return true;
    }

    /**
     * Creates a new email optin and applies the required fields.
     *
     * @param   string      $emailAddress
     * @param   bool        $optedIn
     * @param   Model|null  $product
     * @return  Model
     */
    public function create($emailAddress, $optedIn = false, Model $product = null)
    {
        $optin = $this->getStore()->create('product-email-deployment-optin');
        $this->apply($optin, $emailAddress, $optedIn, $product);
        return $optin;
    }

    /**
     * {@inheritdoc}
     */
    public function preValidate(AbstractModel $optin)
    {
        // Format email address.
        $value = ModelUtility::formatEmailAddress($optin->get('email'));
        $value = (empty($value)) ? null : $value;
        $optin->set('email', $value);
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
    public function postValidate(AbstractModel $optin)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Model $model)
    {
        return 'product-email-deployment-optin' === $model->getType();
    }
}
