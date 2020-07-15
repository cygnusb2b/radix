<?php

namespace AppBundle\Factory;

use As3\Modlr\Models\AbstractModel;

/**
 * Interface defining methods for factories that handle validation.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
interface ValidationFactoryInterface
{
    /**
     * Determines if the model can currently be saved.
     * Will return true if able or will return an Error object is not.
     * The Error instance is throwable. The event subscriber will throw the error by default.
     *
     * @param   AbstractModel   $model
     * @return  true|Error
     */
    public function canSave(AbstractModel $model);

    /**
     * Runs immediately before @see canSave().
     * Allows for appending default values and other operations before the provided model is validated.
     * Ensure operations in this method are SAFE to run MULTIPLE times, as this hook will be called multiple times.
     *
     * @param   AbstractModel   $model
     */
    public function preValidate(AbstractModel $model);

    /**
     * Runs immediately after @see canSave().
     * This method SHOULD not be called more than once.
     *
     * @param   AbstractModel   $model
     */
    public function postValidate(AbstractModel $model);
}
