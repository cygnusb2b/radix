<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Utility\ModelUtility;
use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Events\ModelLifecycleArguments;

/**
 * Handles demographic models.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class DemographicSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public function getEvents()
    {
        return [
            Events::preCommit,
            Events::postDelete,
        ];
    }

    /**
     * Processes demographic models on any commit (create, update, or delete)
     *
     * @param   ModelLifecycleArguments     $args
     */
    public function preCommit(ModelLifecycleArguments $args)
    {
        $model = $args->getModel();
        if (false === $this->shouldProcess($model)) {
            return;
        }
        $this->validateAnswerType($model);

    }

    /**
     * Processes demographic models after deletion.
     *
     * @param   ModelLifecycleArguments     $args
     */
    public function postDelete(ModelLifecycleArguments $args)
    {
        $model = $args->getModel();
        if (false === $this->shouldProcess($model)) {
            return;
        }
        $this->deleteChoices($model);
        $this->deleteMappings($model);
    }

    /**
     * Deletes all choices related to this demographic.
     *
     * @param   Model   $model
     */
    protected function deleteChoices(Model $model)
    {
        $criteria   = ['demographic' => $model->getId()];
        $collection = $model->getStore()->findQuery('demographic-choice', $criteria);
        foreach ($collection as $choice) {
            $choice->delete();
            $choice->save();
        }
    }

    /**
     * Deletes all mappings related to this demographic.
     *
     * @param   Model   $model
     */
    protected function deleteMappings(Model $model)
    {
        $criteria = [
            '$or' => [
                ['demographic' => $model->getId()],
                ['owner'       => $model->getId()],
            ]
        ];
        $collection = $model->getStore()->findQuery('demographic-mapping', $criteria);
        foreach ($collection as $mapping) {
            $mapping->delete();
            $mapping->save();
        }
    }

    /**
     * Validates that the appropriate answer type was set.
     *
     * @param   Model   $model
     */
    protected function validateAnswerType(Model $model)
    {
        $type  = $model->get('answerType');
        $types = ModelUtility::getFormAnswerTypes();
        if (!isset($types[$type])) {
            throw new \InvalidArgumentException(sprintf('The type of "%s" is not a valid demographic answer type. Valid types are "%s"', $type, implode('", "', array_keys($types))));
        }
    }

    /**
     * Determines if this subscriber should handle the model.
     * Must be a demographic model.
     *
     * @param   Model   $model
     * @return  bool
     */
    protected function shouldProcess(Model $model)
    {
        return 'demographic' === $model->getType();
    }
}
