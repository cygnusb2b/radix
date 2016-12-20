<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Question\TypeManager;
use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Events\ModelLifecycleArguments;

/**
 * Handles question models.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class QuestionSubscriber implements EventSubscriberInterface
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
     * Processes question models on any commit (create, update, or delete)
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

        $type = $this->typeManager->getQuestionTypeFor($model->get('questionType'));
        if (null !== $model->get('allowHtml') && false === $type->supportsHtml()) {
            // Prevent allowing HTML if the question type does not support it.
            $model->set('allowHtml', false);
        }

    }

    /**
     * Validates the question.
     *
     * @param   Model   $model
     */
    protected function validate(Model $model)
    {
        $type  = $model->get('questionType');
        if (false === $this->typeManager->hasQuestionTypeFor($type)) {
            $types = $this->typeManager->getQuestionTypes();
            throw new \InvalidArgumentException(sprintf('The type of "%s" is not a valid question type. Valid types are "%s"', $type, implode('", "', array_keys($types))));
        }

        $name = $model->get('name');
        if (empty($name)) {
            throw new \InvalidArgumentException('The question `name` is required.');
        }
    }

    /**
     * Determines if this subscriber should handle the model.
     * Must be a question model.
     *
     * @param   Model   $model
     * @return  bool
     */
    protected function shouldProcess(Model $model)
    {
        return 'question' === $model->getType();
    }
}
