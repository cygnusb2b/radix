<?php

namespace AppBundle\Factory;

/**
 * Abstract factory for AS3 embeds.
 * Must also implement the remaining methods of the validation interface.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
abstract class AbstractEmbedFactory implements ValidationFactoryInterface
{
    /**
     * Determines the embed type this factory supports.
     *
     * @return  string
     */
    abstract protected function getSupportsType();

    /**
     * Gets the unsupported embed type error.
     *
     * @return  Error
     */
    protected function getUnsupportedError()
    {
        return new Error(sprintf('The provided embed model is not supported. Expected an instance of `%s`', $this->getSupportedEmbedType()));
    }

    /**
     * Determines if the embed is supported.
     *
     * @param   Embed   $model
     * @return  bool
     */
    protected function supportsEmbed(Embed $model)
    {
        return $this->getSupportsType() === $model->getName();
    }
}
