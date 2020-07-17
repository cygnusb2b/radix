<?php

namespace AppBundle\Serializer;

interface PublicApiRuleInterface
{
    /**
     * The model class to target.
     *
     * @return  string
     */
    public function getModelClass();

    /**
     * The model type to target.
     *
     * @return  string
     */
    public function getModelType();

    /**
     * Determines if the field should be serialized.
     *
     * @return  bool
     */
    public function shouldSerialize($fieldKey);

    /**
     * Gets a custom serializer closure, or null if it doesn't exist.
     *
     * @return \Closure|null
     */
    public function getCustomSerializer($fieldKey);
}
