<?php

namespace AppBundle\Cloning\Transformer;

use As3\Modlr\Models\Model;

abstract class AbstractTransformer
{
    protected $cloner;

    protected $store;

    public function __construct(Store $store, ModelCloner $cloner)
    {
        $this->store  = $store;
        $this->cloner = $cloner;
    }


    abstract public function getTransformTypes();

    protected function isValidSource(Model $model)
    {
        list($source, $target) = $this->getTransformTypes();
        return $model->getType() === $source;
    }

    protected function isValidTarget(Model $model)
    {
        list($source, $target) = $this->getTransformTypes();
        return $model->getType() === $target;
    }

    protected function validateCreateAndTransform(Model $model)
    {
        if (false === $this->isValidSource($model)) {
            throw new \InvalidArgumentException(sprintf('Unable to start the transform process. The model type "%s" is not a valid source for this transformer.', $model->getType()));
        }
    }

    protected function validateTransform(Model $source, Model $target)
    {
        $this->validateCreateAndTransform($source);
        if (false === $this->isValidTarget($target)) {
            throw new \InvalidArgumentException(sprintf('Unable to start the transform process. The model type "%s" is not a valid target for this transformer.', $target->getType()));
        }
    }
}
