<?php

namespace AppBundle\Factory\Customer;

use AppBundle\Factory\Error;
use AppBundle\Utility\ModelUtility;
use As3\Modlr\Models\AbstractModel;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;

/**
 * Factory for customer accounts.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class CustomerIdentityFactory extends AbstractCustomerFactory
{
    /**
     * {@ineritdoc}
     */
    public function canSave(AbstractModel $customer)
    {
        if (true !== $result = parent::canSave($customer)) {
            return $result;
        }

        $this->preValidate($customer);

        $value = $customer->get('primaryEmail');
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
     * {@ineritdoc}
     */
    public function create(array $attributes = [])
    {
        $customer = parent::create($attributes);
        return $customer;
    }

    /**
     * {@inheritdoc}
     */
    public function preValidate(AbstractModel $customer)
    {
        // Format email address.
        $email = ModelUtility::formatEmailAddress($customer->get('primaryEmail'));
        $customer->set('primaryEmail', $email);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Model $model)
    {
        return 'customer-identity' === $model->getType();
    }

    /**
     * {@inheritdoc}
     */
    protected function createEmptyInstance()
    {
        return $this->getStore()->create('customer-identity');
    }
}
