<?php

namespace AppBundle\Factory\Customer;

use AppBundle\Factory\AbstractModelFactory;
use AppBundle\Factory\Error;
use AppBundle\Factory\SubscriberFactoryInterface;
use AppBundle\Utility\HelperUtility;
use As3\Modlr\Models\AbstractModel;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;

/**
 * Abstract customer factory with common operations for both accounts and identities.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
abstract class AbstractCustomerFactory extends AbstractModelFactory implements SubscriberFactoryInterface
{
    /**
     * @var CustomerAddressFactory
     */
    private $address;

    /**
     * @var CustomerAnswerFactory
     */
    private $answer;

    /**
     * @var CustomerPhoneFactory
     */
    private $phone;

    /**
     * @param   Store                   $store
     * @param   CustomerAddressFactory  $address
     * @param   CustomerPhoneFactory    $phone
     * @param   CustomerAnswerFactory   $answer
     */
    public function __construct(Store $store, CustomerAddressFactory $address, CustomerPhoneFactory $phone, CustomerAnswerFactory $answer)
    {
        parent::__construct($store);
        $this->address = $address;
        $this->phone   = $phone;
        $this->answer  = $answer;
    }

    /**
     * Applies attribute key/value data to the provided customer.
     *
     * @param   Model   $customer
     * @param   array   $attributes
     */
    public function apply(Model $customer, array $attributes = [])
    {
        $metadata = $customer->getMetadata();
        foreach ($attributes as $key => $value) {
            if (true === $metadata->hasAttribute($key)) {
                $customer->set($key, $value);
            }
        }

        $this->setPrimaryAddress($customer, $attributes);
        $this->setPrimaryPhone($customer, $attributes);
        $this->setAnswers($customer, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function canSave(AbstractModel $customer)
    {
        $this->preValidate($customer);
        foreach ($this->getRelatedAddresses($customer) as $address) {
            if (true !== $result = $this->getAddressFactory()->canSave($address)) {
                // Ensure all addresses can be saved.
                return $result;
            }
        }
        foreach ($this->getRelatedAnswers($customer) as $answer) {
            if (true !== $result = $this->getAnswerFactory()->canSave($answer)) {
                // Ensure all answers can be saved.
                return $result;
            }
        }
        foreach ($customer->get('phones') as $phone) {
            if (true !== $result = $this->getPhoneFactory()->canSave($phone)) {
                // Ensure all phones can be saved.
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
        $customer = $this->createEmptyInstance();
        $customer->set('deleted', false);
        $this->apply($customer, $attributes);
        return $customer;
    }

    /**
     * Gets the customer address factory.
     *
     * @return  CustomerAddressFactory
     */
    public function getAddressFactory()
    {
        return $this->address;
    }

    /**
     * Gets the customer answer factory.
     *
     * @return  CustomerAnswerFactory
     */
    public function getAnswerFactory()
    {
        return $this->answer;
    }

    /**
     * Gets the customer phone factory.
     *
     * @return  CustomerPhoneFactory
     */
    public function getPhoneFactory()
    {
        return $this->phone;
    }

    /**
     * Gets all related models for the provided customer (including itself).
     *
     * @param   Model   $customer
     * @return  Model[]
     */
    public function getRelatedModelsFor(Model $customer)
    {
        return array_merge([$customer], $this->getRelatedAddresses($customer), $this->getRelatedAnswers($customer));
    }

    /**
     * {@inheritdoc}
     */
    public function preValidate(AbstractModel $customer)
    {
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
    public function postValidate(AbstractModel $customer)
    {
    }

    /**
     * Saves the provided customer and all its related models, if valid.
     *
     * @param   Model   $customer
     */
    public function save(Model $customer)
    {
        if (true !== $result = $this->canSave($customer)) {
            $result->throwException();
        }
        foreach ($this->getRelatedModelsFor($customer) as $model) {
            $model->save();
        }
    }

    /**
     * Creates a new, unsaved, empty customer model instance.
     *
     * @return  Model
     */
    protected abstract function createEmptyInstance();

    /**
     * This is needed in order to ensure newly created addresses are also accounted for.
     * Modlr really needs to "automatically" append new inverse models to the owner's collection.
     *
     * @param   Model   $customer
     * @param   Model[]
     */
    protected function getRelatedAddresses(Model $customer)
    {
        $addresses = [];
        foreach ($this->getStore()->getModelCache()->getAllForType('customer-address') as $address) {
            if (null === $address->get('customer')) {
                continue;
            }
            if ($address->get('customer')->getId() === $customer->getId()) {
                $addresses[$address->getId()] = $address;
            }
        }
        foreach ($customer->get('addresses') as $address) {
            if (!isset($addresses[$address->getId()])) {
                $addresses[$address->getId()] = $address;
            }
        }
        return $addresses;
    }

    /**
     * This is needed in order to ensure newly created answers are also accounted for.
     * Modlr really needs to "automatically" append new inverse models to the owner's collection.
     *
     * @param   Model   $customer
     * @param   Model[]
     */
    protected function getRelatedAnswers(Model $customer)
    {
        $answers = [];
        foreach ($this->getStore()->getModelCache()->getAll() as $type => $models) {
            if (0 !== stripos($type, 'customer-answer-')) {
                continue;
            }
            foreach ($models as $answer) {
                if (null === $answer->get('customer')) {
                    continue;
                }
                if ($answer->get('customer')->getId() === $customer->getId()) {
                    $answers[$answer->getId()] = $answer;
                }
            }
        }
        foreach ($customer->get('answers') as $answer) {
            if (!isset($answers[$answer->getId()])) {
                $answers[$answer->getId()] = $answer;
            }
        }
        return $answers;
    }

    /**
     * Sets question answers to the customer model.
     *
     * @param   Model   $customer
     * @param   array   $attributes
     */
    private function setAnswers(Model $customer, array $attributes)
    {
        if (false === HelperUtility::isSetArray($attributes, 'answers')) {
            return;
        }

        $questionIds = [];
        foreach ($attributes['answers'] as $questionId => $answerId) {
            if (!HelperUtility::isMongoIdFormat($questionId)) {
                continue;
            }
            $questionIds[] = $questionId;
        }
        if (empty($questionIds)) {
            return;
        }
        $criteria  = ['id' => ['$in' => $questionIds]];
        $questions = $this->getStore()->findQuery('question', $criteria);

        foreach ($questions as $question) {
            $this->getAnswerFactory()->apply($customer, $question, $attributes['answers'][$question->getId()]);
        }
    }

    /**
     * Sets the primary mailing address to the customer model.
     *
     * @todo    Handle when multiple addresses are used.
     * @param   Model   $customer
     * @param   array   $attributes
     */
    private function setPrimaryAddress(Model $customer, array $attributes)
    {
        if (false === HelperUtility::isSetArray($attributes, 'primaryAddress')) {
            return;
        }

        // @todo How do we remove a primary address?
        // @todo This will have to be more "intelligent" once we add additional addresses.
        $primaryAddress = $customer->get('primaryAddress');
        if (true === $customer->getState()->is('new') || null === $primaryAddress) {
            $this->getAddressFactory()->create($customer, $attributes['primaryAddress']);
        } else {
            // @todo Once multiple addresses are supported, will need to make the address factory smart so that new addresses aren't added improperly.
            foreach ($customer->get('addresses') as $address) {
                if ($address->getId() === $primaryAddress->_id) {
                    $this->getAddressFactory()->apply($address, $attributes['primaryAddress']);
                }
            }
        }
    }

    /**
     * Sets the primary phone number to the customer model.
     *
     * @todo    Determine how to upsert!!!
     * @param   Model   $customer
     * @param   array   $attributes
     */
    protected function setPrimaryPhone(Model $customer, array $attributes)
    {
        if (false === HelperUtility::isSetArray($attributes, 'primaryPhone')) {
            return;
        }
        if (false === HelperUtility::isSetNotEmpty($attributes['primaryPhone'], 'number')) {
            return;
        }

        // @todo Needs to re-vamped when support is added for multiple phones.
        $number       = $attributes['primaryPhone']['number'];
        $primaryPhone = $customer->get('primaryPhone');

        if (true === $customer->getState()->is('new') || null === $primaryPhone) {
            $type   = isset($attributes['primaryPhone']['phoneType']) ? $attributes['primaryPhone']['phoneType'] : null;
            $phone  = $customer->createEmbedFor('phones');
            $this->getPhoneFactory()->apply($phone, $number, $type, true);
            $customer->pushEmbed('phones', $phone);
        } else {
            foreach ($customer->get('phones') as $phone) {
                if ($phone->get('number') === $primaryPhone->number) {
                    $this->getPhoneFactory()->apply($phone, $number);
                }
            }
        }
    }
}
