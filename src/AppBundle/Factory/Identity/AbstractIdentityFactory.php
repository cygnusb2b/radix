<?php

namespace AppBundle\Factory\Identity;

use AppBundle\Factory\AbstractModelFactory;
use AppBundle\Factory\Error;
use AppBundle\Factory\SubscriberFactoryInterface;
use AppBundle\Utility\HelperUtility;
use As3\Modlr\Models\AbstractModel;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;

/**
 * Abstract identity factory with common operations for both accounts and internal/external identities.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
abstract class AbstractIdentityFactory extends AbstractModelFactory implements SubscriberFactoryInterface
{
    /**
     * @var IdentityAddressFactory
     */
    private $address;

    /**
     * @var IdentityAnswerFactory
     */
    private $answer;

    /**
     * @var IdentityPhoneFactory
     */
    private $phone;

    /**
     * @param   Store                   $store
     * @param   IdentityAddressFactory  $address
     * @param   IdentityPhoneFactory    $phone
     * @param   IdentityAnswerFactory   $answer
     */
    public function __construct(Store $store, IdentityAddressFactory $address, IdentityPhoneFactory $phone, IdentityAnswerFactory $answer)
    {
        parent::__construct($store);
        $this->address = $address;
        $this->phone   = $phone;
        $this->answer  = $answer;
    }

    /**
     * Applies attribute key/value data to the provided identity.
     *
     * @param   Model   $identity
     * @param   array   $attributes
     */
    public function apply(Model $identity, array $attributes = [])
    {
        $metadata = $identity->getMetadata();
        foreach ($attributes as $key => $value) {
            if (true === $metadata->hasAttribute($key)) {
                $identity->set($key, $value);
            }
        }

        $this->setPrimaryAddress($identity, $attributes);
        $this->setPrimaryPhone($identity, $attributes);
        $this->setAnswers($identity, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function canSave(AbstractModel $identity)
    {
        $this->preValidate($identity);
        foreach ($identity->get('addresses') as $address) {
            if (true !== $result = $this->getAddressFactory()->canSave($address)) {
                // Ensure all addresses can be saved.
                return $result;
            }
        }

        foreach ($identity->get('phones') as $phone) {
            if (true !== $result = $this->getPhoneFactory()->canSave($phone)) {
                // Ensure all phones can be saved.
                return $result;
            }
        }

        foreach ($this->getRelatedAnswers($identity) as $answer) {
            if (true !== $result = $this->getAnswerFactory()->canSave($answer)) {
                // Ensure all answers can be saved.
                return $result;
            }
        }
        return true;
    }

    /**
     * Creates a new identity and applies any root attribute data.
     *
     * @param   array   $attributes
     * @return  Model
     */
    public function create(array $attributes = [])
    {
        $identity = $this->createEmptyInstance();
        $identity->set('deleted', false);
        $this->apply($identity, $attributes);
        return $identity;
    }

    /**
     * Gets the identity address factory.
     *
     * @return  IdentityAddressFactory
     */
    public function getAddressFactory()
    {
        return $this->address;
    }

    /**
     * Gets the identity answer factory.
     *
     * @return  IdentityAnswerFactory
     */
    public function getAnswerFactory()
    {
        return $this->answer;
    }

    /**
     * Gets the identity phone factory.
     *
     * @return  IdentityPhoneFactory
     */
    public function getPhoneFactory()
    {
        return $this->phone;
    }

    /**
     * Gets all related models for the provided identity (including itself).
     *
     * @param   Model   $identity
     * @return  Model[]
     */
    public function getRelatedModelsFor(Model $identity)
    {
        return array_merge([$identity], $this->getRelatedAnswers($identity));
    }

    /**
     * {@inheritdoc}
     */
    public function preValidate(AbstractModel $identity)
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
    public function postValidate(AbstractModel $identity)
    {
    }

    /**
     * Saves the provided identity and all its related models, if valid.
     *
     * @param   Model   $identity
     */
    public function save(Model $identity)
    {
        if (true !== $result = $this->canSave($identity)) {
            $result->throwException();
        }
        foreach ($this->getRelatedModelsFor($identity) as $model) {
            $model->save();
        }
    }

    /**
     * Gets the identity model type this factory supports.
     *
     * @return  string
     */
    abstract public function getSupportsType();

    /**
     * {@inheritdoc}
     */
    public function supports(Model $model)
    {
        return $this->getSupportsType() === $model->getType();
    }

    /**
     * Creates a new, unsaved, empty identity model instance.
     *
     * @return  Model
     */
    protected function createEmptyInstance()
    {
        return $this->getStore()->create($this->getSupportsType());
    }

    /**
     * This is needed in order to ensure newly created answers are also accounted for.
     * Modlr really needs to "automatically" append new inverse models to the owner's collection.
     *
     * @param   Model   $identity
     * @param   Model[]
     */
    protected function getRelatedAnswers(Model $identity)
    {
        $answers = [];
        foreach ($this->getStore()->getModelCache()->getAll() as $type => $models) {
            if (0 !== stripos($type, 'identity-answer-')) {
                continue;
            }
            foreach ($models as $answer) {
                if (null === $answer->get('identity')) {
                    continue;
                }
                if ($answer->get('identity')->getId() === $identity->getId()) {
                    $answers[$answer->getId()] = $answer;
                }
            }
        }
        foreach ($identity->get('answers') as $answer) {
            if (!isset($answers[$answer->getId()])) {
                $answers[$answer->getId()] = $answer;
            }
        }
        return $answers;
    }

    /**
     * Sets question answers to the identity model.
     *
     * @param   Model   $identity
     * @param   array   $attributes
     */
    private function setAnswers(Model $identity, array $attributes)
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
            $this->getAnswerFactory()->apply($identity, $question, $attributes['answers'][$question->getId()]);
        }
    }

    /**
     * Sets the primary mailing address to the identity model.
     *
     * @todo    Handle when multiple addresses are used.
     * @param   Model   $identity
     * @param   array   $attributes
     */
    private function setPrimaryAddress(Model $identity, array $attributes)
    {
        if (false === HelperUtility::isSetArray($attributes, 'primaryAddress')) {
            return;
        }

        $properties = $attributes['primaryAddress'];
        $embedMeta  = $identity->getMetadata()->getEmbed('addresses')->embedMeta;
        $factory    = $this->getAddressFactory();

        // @todo Needs to re-vamped when front-end support is added for multiple addresses.
        $primaryAddress = $identity->get('primaryAddress');

        // Force set to primary, since currently this is all the method supports.
        $properties['isPrimary'] = true;

        if (true === $identity->getState()->is('new') || null === $primaryAddress) {
            // The identity is new, or no address phone was previously set. Create and push.
            $phone = $factory->create($embedMeta, $properties);
            $identity->pushEmbed('addresses', $phone);

        } else {
            // The identity is existing, or a primary address already exists. Determine update or create.
            $create = false;
            if (!isset($properties['identifier'])) {
                // The address is "new" on the front-end. @todo Need to add check to ensure the address value (same country/state, etc) doesn't already exist.
                $create = true;
            } else {
                // Existing address. Attempt to find and update.
                foreach ($identity->get('addresses') as $address) {
                    if ($address->get('identifier') === $properties['identifier']) {
                        // Apply the address attributes to the found address.
                        $factory->apply($address, $properties);
                        return;
                    }
                }
                // At this point, the incoming address has an identifier, but it wasn't found on the identity. Treat as a creation.
                $create = true;
            }

            if (true === $create) {
                $address = $factory->create($embedMeta, $properties);
                $identity->pushEmbed('addresses', $address);
            }
        }
    }

    /**
     * Sets the primary phone number to the identity model.
     *
     * @todo    Determine how to upsert!!!
     * @param   Model   $identity
     * @param   array   $attributes
     */
    protected function setPrimaryPhone(Model $identity, array $attributes)
    {
        if (false === HelperUtility::isSetArray($attributes, 'primaryPhone')) {
            return;
        }
        if (false === HelperUtility::isSetNotEmpty($attributes['primaryPhone'], 'number')) {
            return;
        }

        $properties = $attributes['primaryPhone'];
        $embedMeta  = $identity->getMetadata()->getEmbed('phones')->embedMeta;
        $factory    = $this->getPhoneFactory();

        // @todo Needs to re-vamped when front-end support is added for multiple phones.
        $primaryPhone = $identity->get('primaryPhone');

        // Force set to primary, since currently this is all the method supports.
        $properties['isPrimary'] = true;

        if (true === $identity->getState()->is('new') || null === $primaryPhone) {
            // The identity is new, or no primary phone was previously set. Create and push.
            $phone = $factory->create($embedMeta, $properties);
            $identity->pushEmbed('phones', $phone);

        } else {
            // The identity is existing, or a primary phone already exists. Determine update or create.
            $create = false;
            if (!isset($properties['identifier'])) {
                // The phone is "new" on the front-end. @todo Need to add check to ensure the phone number doesn't already exist.
                $create = true;
            } else {
                // Existing phone. Attempt to find and update.
                foreach ($identity->get('phones') as $phone) {
                    if ($phone->get('identifier') === $properties['identifier']) {
                        // Apply the phone attributes to the found phone.
                        $factory->apply($phone, $properties);
                        return;
                    }
                }
                // At this point, the incoming phone has an identifier, but it wasn't found on the identity. Treat as a creation.
                $create = true;
            }

            if (true === $create) {
                $phone = $factory->create($embedMeta, $properties);
                $identity->pushEmbed('phones', $phone);
            }
        }
    }
}
