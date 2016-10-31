<?php

namespace AppBundle\Factory\Identity;

use AppBundle\Factory\AbstractModelFactory;
use AppBundle\Factory\Error;
use AppBundle\Factory\SubscriberFactoryInterface;
use AppBundle\Question\QuestionAnswerFactory;
use As3\Modlr\Models\AbstractModel;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;

/**
 * Factory for identity answers.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class IdentityAnswerFactory extends AbstractModelFactory implements SubscriberFactoryInterface
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
     * Applies an answer value to an identity for the provided question.
     *
     * @param   Model   $identity
     * @param   Model   $question
     * @param   mixed   $rawAnswerValue
     */
    public function apply(Model $identity, Model $question, $rawAnswerValue)
    {
        if ('question' !== $question->getType()) {
            throw new \InvalidArgumentException('The model is not an instance of a `question` model.');
        }
        if (true === $question->get('deleted')) {
            return;
        }

        if (true === $identity->getState()->is('new') || 0 === count($identity->get('answers'))) {
            $this->handleNewAnswer($identity, $question, $rawAnswerValue);
        } else {
            $this->handleExistingAnswer($identity, $question, $rawAnswerValue);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function canSave(AbstractModel $answer)
    {
        $this->preValidate($answer);
        if (null === $answer->get('identity')) {
            // Ensure a identity has been assigned.
            return new Error('All identity answers must be assigned to a identity.');
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
        return 0 === stripos($model->getType(), 'identity-answer-');
    }


    /**
     * @param   Model   $identity
     * @param   Model   $question
     * @param   mixed   $rawAnswerValue
     */
    private function handleExistingAnswer(Model $identity, Model $question, $rawAnswerValue)
    {
        $answer = $this->answerFactory->createAnswerFor('identity', $question, $rawAnswerValue);

        foreach ($identity->get('answers') as $current) {
            if ($current->get('question')->getId() === $question->getId()) {
                // Answer currently exists on the identity... determine update or remove.
                if (null === $answer) {
                    $current->delete();
                } else {
                    $current->set('value', $answer->get('value'));
                }
                return;
            }
        }
        // Answer doesn't exist on identity, add it.
        if (null !== $answer) {
            $answer->set('identity', $identity);
        }
    }

    /**
     * @param   Model   $identity
     * @param   Model   $question
     * @param   mixed   $rawAnswerValue
     */
    private function handleNewAnswer(Model $identity, Model $question, $rawAnswerValue)
    {
        $answer = $this->answerFactory->createAnswerFor('identity', $question, $rawAnswerValue);
        if (null === $answer) {
            return;
        }
        $answer->set('identity', $identity);
    }
}
