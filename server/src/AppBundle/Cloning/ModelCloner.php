<?php

namespace AppBundle\Cloning;

use AppBundle\Exception\HttpFriendlyException;
use As3\Modlr\Metadata\EmbeddedPropMetadata;
use As3\Modlr\Metadata\EntityMetadata;
use As3\Modlr\Metadata\Interfaces\AttributeInterface;
use As3\Modlr\Metadata\Interfaces\EmbedInterface;
use As3\Modlr\Models\AbstractModel;
use As3\Modlr\Models\Embed;
use As3\Modlr\Models\Model;

/**
 * Clones a model and/or applies attribute/embed values from a source to a target.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class ModelCloner
{
    /**
     * Clones attributes and embeds from one model to another.
     * Relationships are not cloned, as this wouldn't make sense. :)
     *
     * @param   Model   $model
     * @return  Model
     */
    public function cloneFrom(Model $model)
    {
        $clone = $model->getStore()->create($model->getType());
        $this->apply($model, $clone);
        return $clone;
    }

    /**
     * Applies attributes and embeds from a source model to the target model.
     *
     * @param   Model   $source
     * @param   Model   $target
     */
    public function apply(Model $source, Model $target)
    {
        $this->applyAttributes($source, $target);
        $this->applyEmbeds($source, $target);
    }

    /**
     * Applies attributes from the source model to the target model.
     *
     * @param   Model   $source
     * @param   Model   $target
     */
    public function applyAttributes(Model $source, Model $target)
    {
        $this->doApply('handleAttributesFor', $source, $target);
    }

    /**
     * Applies embeds from the source model to the target model.
     *
     * @param   Model   $source
     * @param   Model   $target
     */
    public function applyEmbeds(Model $source, Model $target)
    {
        $this->doApply('handleEmbedsFor', $source, $target);
    }

    /**
     * Applies an attribute value from the source model to the target model.
     *
     * @param   string          $key
     * @param   AbstractModel   $source
     * @param   AbstractModel   $target
     */
    private function applyAttribute($key, AbstractModel $source, AbstractModel $target)
    {
        $value = $source->get($key);
        if (is_object($value)) {
            $value = clone $value;
        }
        $target->set($key, $value);
    }

    /**
     * Applies an embed value from the source model to the target model.
     *
     * @param   string                  $key
     * @param   EmbeddedPropMetadata    $embedPropMeta
     * @param   AbstractModel           $source
     * @param   AbstractModel           $target
     */
    private function applyEmbed($key, EmbeddedPropMetadata $embedPropMeta, AbstractModel $source, AbstractModel $target)
    {
        if (true === $embedPropMeta->isOne()) {
            if (null === $embed = $source->get($key)) {
                return;
            }
            $targetEmbed = $target->createEmbedFor($key);
            $this->applyEmbedValues($embed, $targetEmbed);
            $target->set($key, $targetEmbed);
        } else {
            foreach ($source->get($key) as $embed) {
                $targetEmbed = $target->createEmbedFor($key);
                $this->applyEmbedValues($embed, $targetEmbed);
                $target->pushEmbed($key, $targetEmbed);
            }
        }
    }

    /**
     * Applies the values of an embedded source model to the target embedded model.
     *
     * @param   Embed   $source
     * @param   Embed   $target
     */
    private function applyEmbedValues(Embed $source, Embed $target)
    {
        foreach ($source->getMetadata()->getAttributes() as $key => $attrMeta) {
            $this->applyAttribute($key, $source, $target);
        }
        foreach ($source->getMetadata()->getEmbeds() as $key => $embedPropMeta) {
            $this->applyEmbed($key, $embedPropMeta, $source, $target);
        }
    }

    /**
     * Determines if the source model and the target model share any common mixins.
     *
     * @param   Model   $source
     * @param   Model   $target
     * @return  bool
     */
    private function areModelsSameMixin(Model $source, Model $target)
    {
        $metas = $this->extractCommonMixins($source, $target);
        return !empty($metas);
    }

    /**
     * Determines if the source model and the target model share the same parent.
     *
     * @param   Model   $source
     * @param   Model   $target
     * @return  bool
     */
    private function areModelsSameParent(Model $source, Model $target)
    {
        $sMeta = $source->getMetadata();
        $tMeta = $target->getMetadata();

        if (false === $sMeta->isChildEntity() || false === $tMeta->isChildEntity()) {
            return false;
        }

        return $sMeta->getParentEntityType() === $tMeta->getParentEntityType();
    }

    /**
     * Determines if the source model and the target model are the same type.
     *
     * @param   Model   $source
     * @param   Model   $target
     * @return  bool
     */
    private function areModelsSameType(Model $source, Model $target)
    {
        return $source->getType() === $target->getType();
    }

    /**
     * Executes the apply handling use the provided handling method.
     *
     * @param   string  $handleMethod
     * @param   Model   $source
     * @param   Model   $target
     */
    private function doApply($handleMethod, Model $source, Model $target)
    {
        if (true === $this->areModelsSameType($source, $target)) {

            $metadata = $source->getMetadata();
            $this->{$handleMethod}($metadata, $source, $target);

        } elseif (true === $this->areModelsSameParent($source, $target)) {

            $metadata = $this->getParentMetadata($source);
            $this->{$handleMethod}($metadata, $source, $target);

        } elseif (true === $this->areModelsSameMixin($source, $target)) {

            $metas = $this->extractCommonMixins($source, $target);
            foreach ($metas as $metadata) {
                $this->{$handleMethod}($metadata, $source, $target);
            }

        } else {
            throw $this->getUnsupportedException();
        }
    }

    /**
     * Extracts the mixin metadata for the shared mixins between the provided models.
     *
     * @param   Model   $source
     * @param   Model   $target
     */
    private function extractCommonMixins(Model $source, Model $target)
    {
        $metas = [];
        $sMeta = $source->getMetadata();
        $tMeta = $target->getMetadata();

        foreach ($sMeta->getMixins() as $name => $mixinMeta) {
            if ($tMeta->hasMixin($name)) {
                $metas[$name] = $mixinMeta;
            }
        }
        return $metas;
    }

    /**
     * Gets the parent metadata for the provided model.
     *
     * @param   Model   $model
     * @return  EntityMetadata
     */
    private function getParentMetadata(Model $model)
    {
        return $model->getStore()->getMetadataForType($model->getMetadata()->getParentEntityType());
    }

    /**
     * @return  HttpFriendlyException
     */
    private function getUnsupportedException()
    {
        return new HttpFriendlyException('Applying values from a source model to a target, in this fashion, is either not supported or net yet implemented.', 501);
    }

    /**
     * Handles applying attributes, based on the provided metadata, from a source model to a target.
     *
     * @param   AttributeInterface  $metadata
     * @param   Model               $source
     * @param   Model               $target
     */
    private function handleAttributesFor(AttributeInterface $metadata, Model $source, Model $target)
    {
        foreach ($metadata->getAttributes() as $key => $attrMeta) {
            $this->applyAttribute($key, $source, $target);
        }
    }

    /**
     * Handles applying embeds, based on the provided metadata, from a source model to a target.
     *
     * @param   EmbedInterface  $metadata
     * @param   Model           $source
     * @param   Model           $target
     */
    private function handleEmbedsFor(EmbedInterface $metadata, Model $source, Model $target)
    {
        foreach ($metadata->getEmbeds() as $key => $embedPropMeta) {
            $this->applyEmbed($key, $embedPropMeta, $source, $target);
        }
    }
}
