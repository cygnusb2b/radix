<?php

namespace AppBundle\Serializer;

use AppBundle\Serializer\PublicApiRules as Rules;
use As3\Modlr\Metadata\AttributeMetadata;
use As3\Modlr\Metadata\EmbeddedPropMetadata;
use As3\Modlr\Metadata\FieldMetadata;
use As3\Modlr\Metadata\EmbedMetadata;
use As3\Modlr\Metadata\RelationshipMetadata;
use As3\Modlr\Models\Collections\Collection;
use As3\Modlr\Models\Collections\EmbedCollection;
use As3\Modlr\Models\AbstractModel;
use As3\Modlr\Models\Embed;
use As3\Modlr\Models\Model;

/**
 * Serializes models for use with the application's public API.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class PublicApiSerializer
{
    private $depth = 0;

    private $maxDepth = 1;

    private $rules = [];

    public function __construct()
    {
        $this->setRules();
    }

    /**
     * Adds a serialization rule for a model class/type.
     *
     * @param   PublicApiRuleInterface  $rule
     * @return  self
     */
    public function addRule(PublicApiRuleInterface $rule)
    {
        $this->rules[$rule->getModelClass()][$rule->getModelType()] = $rule;
        return $this;
    }

    /**
     * Determines if a serialization rule exists for the provided model.
     *
     * @param   AbstractModel   $model
     * @return  bool
     */
    public function hasRule(AbstractModel $model)
    {
        return null !== $this->getRule($model);
    }

    /**
     * Gets a serialization rule for the provided model, if it exists.
     *
     * @param   AbstractModel   $model
     * @return  PublicApiRuleInterface|null
     */
    public function getRule(AbstractModel $model)
    {
        $type  = $model instanceof Embed ? $model->getName() : $model->getType();
        $class = get_class($model);
        return isset($this->rules[$class][$type]) ? $this->rules[$class][$type] : null;
    }

    public function resetMaxDepth()
    {
        $this->maxDepth = 1;
        return $this;
    }

    /**
     * Serializes a Model object into a "flattened" array.
     *
     * @param   Model|null          $model
     * @return  array
     */
    public function serialize(Model $model = null)
    {
        $serialized['data'] = null;
        if (null !== $model) {
            $serialized['data'] = $this->serializeModel($model);
            ksort($serialized['data']);
        }
        $this->resetMaxDepth();
        return $serialized;
    }

    /**
     * Serializes an array of Model objects into the appropriate format.
     *
     * @param   Model[]             $models
     * @return  array
     */
    public function serializeArray(array $models)
    {
        $serialized = [];
        // Add pagination at some point??
        // if (0 === $this->depth && !empty($totalCount)) {
        //     $serialized = $this->appendPaginationLinks($adapter, $totalCount, $serialized);
        // }
        $serialized = [];
        foreach ($models as $model) {
            $data = $this->serializeModel($model);
            ksort($data);
            $serialized[] = $data;
        }

        if (1 === $this->depth) {
            $this->resetMaxDepth();
        }
        return $serialized;
    }

    public function setMaxDepth($depth)
    {
        $this->maxDepth = (integer) $depth;
        return $this;
    }

    /**
     * Determines if the field for the provided model has a custom serializer function.
     *
     * @param   AbstractModel   $model
     * @param   FieldMetadata   $fieldMeta
     * @return  \Closure|null
     */
    private function getCustomSerializer(AbstractModel $model, FieldMetadata $fieldMeta)
    {
        return $this->getRule($model)->getCustomSerializer($fieldMeta->getKey());
    }

    /**
     * Increases the serializer depth.
     *
     * @return  self
     */
    private function increaseDepth()
    {
        $this->depth++;
        return $this;
    }

    /**
     * Decreases the serializer depth.
     *
     * @return  self
     */
    private function decreaseDepth()
    {
        if ($this->depth > 0) {
            $this->depth--;
        }
        return $this;
    }

    /**
     * Serializes an attribute value.
     *
     * @param   mixed               $value
     * @param   AttributeMetadata   $attrMeta
     * @return  mixed
     */
    private function serializeAttribute($value, AttributeMetadata $attrMeta)
    {
        if ('date' === $attrMeta->dataType && $value instanceof \DateTime) {
            $milliseconds = sprintf('%03d', round($value->format('u') / 1000, 0));
            return gmdate(sprintf('Y-m-d\TH:i:s.%s\Z', $milliseconds), $value->getTimestamp());
        }
        if ('array' === $attrMeta->dataType && empty($value)) {
            return [];
        }
        if ('object' === $attrMeta->dataType) {
            return (object) $value;
        }
        return $value;
    }

    /**
     * Serializes an embed value.
     *
     * @param   Embed|EmbedCollection|null  $value
     * @param   EmbeddedPropMetadata        $embeddedPropMeta
     * @return  array|null
     */
    private function serializeEmbed($value, EmbeddedPropMetadata $embeddedPropMeta)
    {
        $embedMeta = $embeddedPropMeta->embedMeta;
        if (true === $embeddedPropMeta->isOne()) {
            return $this->serializeEmbedOne($embedMeta, $value);
        }
        return $this->serializeEmbedMany($embedMeta, $value);
    }

    /**
     * Serializes an embed one value.
     *
     * @param   EmbedMetadata   $embedMeta
     * @param   Embed|null      $embed
     * @return  array|null
     */
    private function serializeEmbedOne(EmbedMetadata $embedMeta, Embed $embed = null)
    {
        if (null === $embed) {
            return;
        }
        $serialized = [];
        foreach ($embedMeta->getAttributes() as $key => $attrMeta) {
            if (false === $this->shouldSerialize($embed, $attrMeta)) {
                continue;
            }
            $serialized[$key] = $this->serializeAttribute($embed->get($key), $attrMeta);
        }
        foreach ($embedMeta->getEmbeds() as $key => $embeddedPropMeta) {
            if (false === $this->shouldSerialize($embed, $embeddedPropMeta)) {
                continue;
            }
            $serialized[$key] = $this->serializeEmbed($embed->get($key), $embeddedPropMeta);
        }

        return empty($serialized) ? null : $serialized;
    }

    /**
     * Serializes an embed many value.
     *
     * @param   EmbedMetadata   $embedMeta
     * @param   EmbedCollection $embed
     * @return  array
     */
    private function serializeEmbedMany(EmbedMetadata $embedMeta, EmbedCollection $collection)
    {
        $serialized = [];
        foreach ($collection as $embed) {
            if (!$embed instanceof Embed) {
                continue;
            }
            $serialized[] = $this->serializeEmbedOne($embedMeta, $embed);
        }
        return $serialized;
    }

    /**
     * Serializes a has-many relationship value
     *
     * @param   Model                   $owner
     * @param   Model[]|null            $models
     * @return  array
     */
    private function serializeHasMany(Model $owner, array $models = null)
    {
        $serialized = [];
        if (!empty($models)) {
            $serialized = $this->serializeArray($models);
        }
        return $serialized;
    }

    /**
     * Serializes a has-one relationship value
     *
     * @param   Model                   $owner
     * @param   Model|null              $model
     * @return  array
     */
    private function serializeHasOne(Model $owner, Model $model = null)
    {
        $serialized = new \stdClass();
        if (null !== $model) {
            $serialized = $this->serializeModel($model);
            ksort($serialized);
        }
        return $serialized;
    }

    /**
     * Serializes the "interior" of a model.
     * This is the serialization that takes place outside of a "data" container.
     * Can be used for root model and relationship model serialization.
     *
     * @param   Model   $model
     * @return  array
     */
    private function serializeModel(Model $model)
    {
        $metadata = $model->getMetadata();
        $serialized = [
            '_id'    => $model->getState()->is('new') ? null : $model->getId(),
            '_type'  => $model->getType(),
        ];
        if ($this->depth > $this->maxDepth) {
            return $serialized;
        }

        // Attributes.
        foreach ($metadata->getAttributes() as $key => $attrMeta) {
            if (false === $this->shouldSerialize($model, $attrMeta)) {
                continue;
            }
            $serializer = $this->getCustomSerializer($model, $attrMeta);
            $value = $model->get($key);
            $serialized[$key] = $serializer ? $serializer($model, $value) : $this->serializeAttribute($value, $attrMeta);
        }

        // Embeds.
        foreach ($metadata->getEmbeds() as $key => $embeddedPropMeta) {
            if (false === $this->shouldSerialize($model, $embeddedPropMeta)) {
                continue;
            }
            $serializer = $this->getCustomSerializer($model, $embeddedPropMeta);
            $value = $model->get($key);
            $serialized[$key] = $serializer ? $serializer($model, $value) : $this->serializeEmbed($value, $embeddedPropMeta);
        }

        // Relationships.
        $model->enableCollectionAutoInit(false);
        $this->increaseDepth();
        foreach ($metadata->getRelationships() as $key => $relMeta) {
            if (false === $this->shouldSerialize($model, $relMeta)) {
                continue;
            }
            $serializer = $this->getCustomSerializer($model, $relMeta);
            $relationship = $model->get($key);
            $serialized[$key] = $serializer ? $serializer($model, $relationship) : $this->serializeRelationship($model, $relationship, $relMeta);
        }
        $this->decreaseDepth();
        $model->enableCollectionAutoInit(true);

        return $serialized;
    }

    /**
     * Serializes a relationship value
     *
     * @param   Model                       $owner
     * @param   Model|Model[]|null          $relationship
     * @param   RelationshipMetadata        $relMeta
     * @return  array
     */
    private function serializeRelationship(Model $owner, $relationship = null, RelationshipMetadata $relMeta)
    {
        if ($relMeta->isOne()) {
            if (is_array($relationship)) {
                throw new \RuntimeException('Invalid relationship value.');
            }
            $serialized = $this->serializeHasOne($owner, $relationship);
        } elseif (is_array($relationship) || null === $relationship) {
            $serialized = $this->serializeHasMany($owner, $relationship);
        } else {
            throw new \RuntimeException('Invalid relationship value.');
        }

        // @todo Add linking inside public API??
        // $ownerMeta = $owner->getMetadata();
        // $serialized['links'] = [
        //     'self'      => $adapter->buildUrl($ownerMeta, $owner->getId(), $relMeta->getKey()),
        //     'related'   => $adapter->buildUrl($ownerMeta, $owner->getId(), $relMeta->getKey(), true),
        // ];
        return $serialized;
    }

    /**
     * Sets the default rules for the serializer.
     *
     * @return  self
     */
    private function setRules()
    {
        $this->addRule(new Rules\CoreAccountRule());
        $this->addRule(new Rules\CoreApplicationRule());
        $this->addRule(new Rules\IdentityAccountRule());
        $this->addRule(new Rules\QuestionRule());
        return $this;
    }

    /**
     * Determines if the field for the provided model should be serialized.
     *
     * @param   AbstractModel   $model
     * @param   FieldMetadata   $fieldMeta
     * @return  bool
     */
    private function shouldSerialize(AbstractModel $model, FieldMetadata $fieldMeta)
    {
        if (false === $fieldMeta->shouldSerialize()) {
            return false;
        }
        if (null !== $rule = $this->getRule($model)) {
            return $rule->shouldSerialize($fieldMeta->getKey());
        }
        return true;
    }
}
