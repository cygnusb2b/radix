<?php

namespace AppBundle\Factory;

use AppBundle\Question\QuestionAnswerFactory;
use AppBundle\Utility\LocaleUtility;
use As3\Modlr\Models\Model;

/**
 * Factory for customer answers.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class CustomerAnswerFactory extends AbstractModelFactory
{
    /**
     * @var QuestionAnswerFactory
     */
    private $answerFactory;

    /**
     * @param   QuestionAnswerFactory   $answerFactory
     */
    public function __construct(QuestionAnswerFactory $answerFactory)
    {
        $this->answerFactory = $answerFactory;
        $this->setStore($answerFactory->getStore());
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
            $answer = $this->answerFactory->createAnswerFor('customer', $question, $rawAnswerValue);
            if (null === $answer) {
                return;
            }
            $answer->set('customer', $customer);
        } else {

            $new = $this->answerFactory->createAnswerFor('customer', $question, $rawAnswerValue);
            foreach ($customer->get('answers') as $current) {
                if ($current->get('question')->getId() === $question->getId()) {
                    // Answer currently exists on the customer... determine update or remove.
                    if (null === $new) {
                        $current->delete();
                    } else {
                        $current->set('value', $new->get('value'));
                    }
                    return;
                }
            }
            // Answer doesn't exist on customer, add it.
            if (null !== $new) {
                $new->set('customer', $customer);
            }
        }
    }

    /**
     * Determines if the customer answer model can be saved.
     *
     * @param   Model   $answer
     * @return  true|Error
     */
    public function canSave(Model $answer)
    {
        $this->preValidate($answer);
        if (null === $answer->get('customer')) {
            // Ensure a customer has been assigned.
            return new Error('All customer answers must be assigned to a customer.');
        }
        return true;
    }

    /**
     * Actions that always run (during save) before validation occurs.
     *
     * @param   Model   $answer
     */
    public function preValidate(Model $answer)
    {
    }

    /**
     * Actions that always run (during save) after validation occurs.
     *
     * @param   Model   $answer
     */
    public function postValidate(Model $answer)
    {
    }
}
