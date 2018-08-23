<?php

namespace AppBundle\Factory;

use AppBundle\Question\QuestionAnswerFactory;
use As3\Modlr\Models\AbstractModel;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;

/**
 * Factory for input answers (via submissions).
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class InputAnswerFactory extends AbstractModelFactory implements SubscriberFactoryInterface
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
     * Applies an answer value to a submission for the provided question.
     *
     * @param   Model   $submission
     * @param   Model   $question
     * @param   mixed   $rawAnswerValue
     */
    public function apply(Model $submission, Model $question, $rawAnswerValue)
    {
        if ('question' !== $question->getType()) {
            throw new \InvalidArgumentException('The model is not an instance of a `question` model.');
        }
        if (true === $question->get('deleted')) {
            return;
        }

        if (false === $submission->getState()->is('new')) {
            throw new \RuntimeException('You cannot apply answers to an existing submission');
        }

        $answer = $this->answerFactory->createAnswerFor('input', $question, $rawAnswerValue);
        if (null === $answer) {
            return;
        }
        $answer->set('submission', $submission);
    }

    /**
     * {@inheritdoc}
     */
    public function canSave(AbstractModel $answer)
    {
        $this->preValidate($answer);
        if (null === $answer->get('submission')) {
            // Ensure a customer has been assigned.
            return new Error('All input answers must be assigned to a submission.');
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
        return 0 === stripos($model->getType(), 'input-answer-');
    }
}
