<?php

namespace AppBundle\Serializer\PublicApiRules;

use AppBundle\Serializer\PublicApiRuleInterface;

abstract class AbstractRule
{
    /**
     * Gets all registered custom field serializers.
     * Must be overloaded by the implementing class in order to set.
     *
     * @return \Closure[]
     */
    protected function getCustomFieldSerializers()
    {
        return [];
    }

    /**
     * Determines the fields to exclude. Is used with include all by default is true.
     *
     * @return  array
     */
    abstract protected function getExcludeFields();

    /**
     * Determines the fields to include. Is used with include all by default is false.
     *
     * @return  array
     */
    abstract protected function getIncludeFields();

    /**
     * Determines whether to include all fields by default.
     *
     * @return  bool
     */
    abstract protected function shouldIncludeAll();

    /**
     * {@inheritdoc}
     */
    public function shouldSerialize($fieldKey)
    {
        if (true === $this->shouldIncludeAll()) {
            $fields = $this->getExcludeFields();
            return !isset($fields[$fieldKey]);
        }
        $fields = $this->getIncludeFields();
        return isset($fields[$fieldKey]);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomSerializer($fieldKey)
    {
        if (false === $this->shouldSerialize($fieldKey)) {
            return;
        }
        $custom = $this->getCustomFieldSerializers();
        if (isset($custom[$fieldKey]) && $custom[$fieldKey] instanceof \Closure) {
            return $custom[$fieldKey];
        }
    }
}
