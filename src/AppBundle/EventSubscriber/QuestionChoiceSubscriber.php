<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Question\TypeManager;
use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Events\ModelLifecycleArguments;

/**
 * Handles question-choice models.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class QuestionChoiceSubscriber implements EventSubscriberInterface
{
    /**
     * @var TypeManager
     */
    private $typeManager;

    /**
     * @param   TypeManager     $typeManager
     */
    public function __construct(TypeManager $typeManager)
    {
        $this->typeManager = $typeManager;
    }

    /**
     * {@inheritDoc}
     */
    public function getEvents()
    {
        return [
            Events::preCommit,
        ];
    }

    /**
     * Processes question-choice models on any commit (create, update, or delete)
     *
     * @param   ModelLifecycleArguments     $args
     */
    public function preCommit(ModelLifecycleArguments $args)
    {
        $model = $args->getModel();
        if (false === $this->shouldProcess($model)) {
            return;
        }

        $model->set('name', trim($model->get('name')));

        $this->validate($model);
        $this->setFullName($model);
    }

    /**
     * Sets the full name (question name + choice name).
     *
     * @param   Model   $model
     */
    protected function setFullName(Model $model)
    {
        $model->set('fullName', sprintf('%s: %s', $model->get('question')->get('name'), $model->get('name')));
    }

    /**
     * Validates that the question choice.
     *
     * @param   Model   $model
     */
    protected function validate(Model $model)
    {
        if (null === $model->get('question')) {
            throw new \InvalidArgumentException('All question choices must be linked to a question.');
        }

        $type  = $model->get('choiceType');
        $types = $this->typeManager->getQuestionChoiceTypes();
        if (!isset($types[$type])) {
            throw new \InvalidArgumentException(sprintf('The provided question choice type "%s" is not valid. Valid types are "%s"', $type, implode(', ', array_keys($types))));
        }

        $name = $model->get('name');
        if (empty($name)) {
            throw new \InvalidArgumentException('The question choice `name` is required.');
        }


    }

    /**
     * Determines if this subscriber should handle the model.
     * Must be a question-choice model.
     *
     * @param   Model   $model
     * @return  bool
     */
    protected function shouldProcess(Model $model)
    {
        return 'question-choice' === $model->getType();
    }
}
