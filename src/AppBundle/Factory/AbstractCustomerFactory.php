<?php

namespace AppBundle\Factory;

use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;
use AppBundle\Utility\HelperUtility;

/**
 * Abstract customer factory with common operations for both accounts and identities.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
abstract class AbstractCustomerFactory extends AbstractModelFactory
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
     * @param   CustomerAddressFactory  $address
     * @param   CustomerPhoneFactory    $phone
     * @param   CustomerAnswerFactory   $answer
     */
    public function __construct(CustomerAddressFactory $address, CustomerPhoneFactory $phone, CustomerAnswerFactory $answer)
    {
        $this->address = $address;
        $this->phone   = $phone;
        $this->answer  = $answer;
    }

    public function preValidate(Model $customer)
    {

    }

    public function postValidate(Model $customer)
    {

    }

    public function canSave(Model $customer)
    {
        $this->preValidate($customer);
        foreach ($this->getRelatedAddresses($customer) as $address) {
            if (true !== $result = $this->getAddressFactory()->canSave($address)) {
                // Ensure all addresses can be saved.
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
        $metadata = $customer->getMetadata();
        $customer->set('deleted', false);
        foreach ($attributes as $key => $value) {
            if (true === $metadata->hasAttribute($key)) {
                $customer->set($key, $value);
            }
        }

        $this->setPrimaryAddress($customer, $attributes);
        $this->setPrimaryPhone($customer, $attributes);

        $this->setAnswers($customer, $attributes);

        return $customer;
    }

    /**
     * Gets the customer address factory.
     *
     * @return  CustomerAddressFactory
     */
    public function getAddressFactory()
    {
        $this->address->setStore($this->getStore());
        return $this->address;
    }

    /**
     * Gets the customer answer factory.
     *
     * @return  CustomerAnswerFactory
     */
    public function getAnswerFactory()
    {
        $this->answer->setStore($this->getStore());
        return $this->answer;
    }

    /**
     * Gets the customer phone factory.
     *
     * @return  CustomerPhoneFactory
     */
    public function getPhoneFactory()
    {
        $this->phone->setStore($this->getStore());
        return $this->phone;
    }

    public function getRelatedModelsFor(Model $customer)
    {
        return array_merge([$customer], $this->getRelatedAddresses($customer), $this->getRelatedAnswers($customer));
    }

    /**
     * Creates a new, unsaved, empty customer model instance.
     *
     * @return  Model
     */
    protected abstract function createEmptyInstance();

    /**
     * @param   Model   $customer
     * @param   Model[]
     */
    protected function getRelatedAddresses(Model $customer)
    {
        $addresses = [];
        if (true === $customer->getState()->is('new')) {
            foreach ($this->getStore()->getModelCache()->getAllForType('customer-address') as $address) {
                if ($address->get('customer')->getId() === $customer->getId()) {
                    $addresses[] = $address;
                }
            }
            return $addresses;
        }
        return $customer->get('addresses');
    }

    /**
     * @param   Model   $customer
     * @param   Model[]
     */
    protected function getRelatedAnswers(Model $customer)
    {
        $answers = [];
        if (true === $customer->getState()->is('new')) {
            foreach ($this->getStore()->getModelCache()->getAll('customer-answer') as $type => $models) {
                if (0 !== stripos($type, 'customer-answer-')) {
                    continue;
                }
                foreach ($models as $answer) {
                    if ($answer->get('customer')->getId() === $customer->getId()) {
                        $answers[] = $answer;
                    }
                }
            }
            return $answers;
        }
        return $customer->get('answers');
    }

    /**
     * Sets question answers to the customer model.
     *
     * @todo    Determine how to upsert!!!
     * @param   Model   $customer
     * @param   array   $attributes
     */
    private function setAnswers(Model $customer, array $attributes)
    {
        if (false === HelperUtility::isSetArray($attributes, 'answers')) {
            return;
        }

        // @todo This needs to upsert... so, if an answer for the question is already found, update it, else create it.
        foreach ($attributes['answers'] as $questionId => $answerId) {
            if (!HelperUtility::isMongoIdFormat($questionId)) {
                continue;
            }
            $questionIds[] = $questionId;
        }
        $criteria  = ['id' => ['$in' => $questionIds]];
        $questions = $this->getStore()->findQuery('question', $criteria);
        foreach ($questions as $question) {
            $this->getAnswerFactory()->create($customer, $question, $attributes['answers'][$question->getId()]);
        }
    }

    /**
     * Sets the primary mailing address to the customer model.
     *
     * @todo    Determine how to upsert!!!
     * @param   Model   $customer
     * @param   array   $attributes
     */
    private function setPrimaryAddress(Model $customer, array $attributes)
    {
        if (false === HelperUtility::isSetArray($attributes, 'primaryAddress')) {
            return;
        }
        // @todo This needs to upsert... so, if no primary address found, create new and set, if primary address found, update it.
        $this->getAddressFactory()->create($customer, $attributes['primaryAddress']);
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
        $number = $attributes['primaryPhone']['number'];
        $type   = isset($attributes['primaryPhone']['phoneType']) ? $attributes['primaryPhone']['phoneType'] : null;

        $phone  = $customer->createEmbedFor('phones');

        // @todo This needs to upsert... so, if no primary address found, create new and set, if primary address found, update it.
        $this->getPhoneFactory()->create($phone, $number, $type, true);
        $customer->pushEmbed('phones', $phone);
    }
}
