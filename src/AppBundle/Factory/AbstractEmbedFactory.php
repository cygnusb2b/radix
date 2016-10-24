<?php

namespace AppBundle\Factory;

use As3\Modlr\Metadata\EmbedMetadata;
use As3\Modlr\Models\Embed;

/**
 * Abstract factory for AS3 embeds.
 * Must also implement the remaining methods of the validation interface.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
abstract class AbstractEmbedFactory extends AbstractModelFactory implements ValidationFactoryInterface
{
    /**
     * Applies attribute key/value data to the provided embed.
     *
     * @param   Embed   $embed
     * @param   array   $attributes
     * @return  Embed   $embed
     */
    public function apply(Embed $embed, array $attributes = [])
    {
        if (false === $this->supportsEmbed($embed)) {
            $this->getUnsupportedError()->throwException();
        }

        $metadata = $embed->getMetadata();
        foreach ($attributes as $key => $value) {
            if ('identifier' === $key) {
                // Do not reset the identifier.
                continue;
            }
            if (true === $metadata->hasAttribute($key)) {
                $embed->set($key, $value);
            }
        }
        return $embed;
    }

    /**
     * Creates a new embed instance for the provided metadata.
     *
     * @param   EmbedMetadata   $embedMeta
     * @param   array           $attributes
     * @return  Embed
     */
    public function create(EmbedMetadata $embedMeta, array $attributes = [])
    {
        if (false === $this->supportsMetadata($embedMeta)) {
            $this->getUnsupportedError()->throwException();
        }

        $toLoad = [];
        if ($embedMeta->hasAttribute('identifier')) {
            $identifier = new \MongoId();
            $toLoad['identifier'] = (string) $identifier;
        }

        $embed = $this->getStore()->loadEmbed($embedMeta, $toLoad);
        $embed->getState()->setNew();
        $this->apply($embed, $attributes);
        return $embed;
    }

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

    /**
     * Determines if the embed metadata is supported.
     *
     * @param   EmbedMetadata   $model
     * @return  bool
     */
    protected function supportsMetadata(EmbedMetadata $meta)
    {
        return $this->getSupportsType() === $meta->name;
    }
}
