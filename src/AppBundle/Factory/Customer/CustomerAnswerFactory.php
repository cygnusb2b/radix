<?php

namespace AppBundle\Factory\Customer;

use AppBundle\Factory\AbstractModelFactory;
use AppBundle\Factory\Error;
use AppBundle\Factory\SubscriberFactoryInterface;
use AppBundle\Question\QuestionAnswerFactory;
use AppBundle\Utility\LocaleUtility;
use As3\Modlr\Models\AbstractModel;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;

/**
 * Factory for customer answers.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class CustomerAnswerFactory extends AbstractModelFactory implements SubscriberFactoryInterface
{
    /**
     * @var QuestionAnswerFactory
     */
    private $answerFactory;

    /**
     * @param   Store                   $store
     * @param   QuestionAnswerFactory   $answerFactory
     */
    public function __construct(Store $store, QuestionAnswerFactory $answerFactory)
    {
        parent::__construct($store);
        $this->answerFactory = $answerFactory;
    }

    /**
     * Applies an answer value to a customer for the provided question.
     *
     * @param   Model   $customer
     * @param   Model   $question
     * @param   mixed   $rawAnswerValue
     */
    public function apply(Model $customer, Model $question, $rawAnswerValue)
    {
        if ('question' !== $question->getType()) {
            throw new \InvalidArgumentException('The model is not an instance of a `question` model.');
        }
        if (true === $question->get('deleted')) {
            return;
        }

        if (true === $customer->getState()->is('new') || 0 === count($customer->get('answers'))) {
            $this->handleNewAnswer($customer, $question, $rawAnswerValue);
        } else {
            $this->handleExistingAnswer($customer, $question, $rawAnswerValue);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function canSave(AbstractModel $answer)
    {
        $this->preValidate($answer);
        if (null === $answer->get('customer')) {
            // Ensure a customer has been assigned.
            return new Error('All customer answers must be assigned to a customer.');
        }
        return true;
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
    public function postValidate(AbstractModel $answer)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function preValidate(AbstractModel $answer)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Model $model)
    {
        return 0 === stripos($model->getType(), 'customer-answer-');
    }


    /**
     * @param   Model   $customer
     * @param   Model   $question
     * @param   mixed   $rawAnswerValue
     */
    private function handleExistingAnswer(Model $customer, Model $question, $rawAnswerValue)
    {
        $answer = $this->answerFactory->createAnswerFor('customer', $question, $rawAnswerValue);

        foreach ($customer->get('answers') as $current) {
            if ($current->get('question')->getId() === $question->getId()) {
                // Answer currently exists on the customer... determine update or remove.
                if (null === $answer) {
                    $current->delete();
                } else {
                    $current->set('value', $answer->get('value'));
                }
                return;
            }
        }
        // Answer doesn't exist on customer, add it.
        if (null !== $answer) {
            $answer->set('customer', $customer);
        }
    }

    /**
     * @param   Model   $customer
     * @param   Model   $question
     * @param   mixed   $rawAnswerValue
     */
    private function handleNewAnswer(Model $customer, Model $question, $rawAnswerValue)
    {
        $answer = $this->answerFactory->createAnswerFor('customer', $question, $rawAnswerValue);
        if (null === $answer) {
            return;
        }
        $answer->set('customer', $customer);
    }
}
