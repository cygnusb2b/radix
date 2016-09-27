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
     * Creates a new customer answer for a customer and question.
     *
     * @param   Model   $customer
     * @param   Model   $question
     * @param   mixed   $rawAnswerValue
     * @return  Model|null
     */
    public function create(Model $customer, Model $question, $rawAnswerValue)
    {
        if ('question' !== $question->getType()) {
            throw new \InvalidArgumentException('The model is not an instance of a `question` model.');
        }
        if (true === $question->get('deleted')) {
            return;
        }

        $answer = $this->answerFactory->createAnswerFor('customer', $question, $rawAnswerValue);
        if (null === $answer) {
            return;
        }
        $answer->set('customer', $customer);
        return $answer;
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
