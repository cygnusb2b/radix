<?php

namespace AppBundle\Serializer\PublicApiRules;

use AppBundle\Serializer\PublicApiRuleInterface;

abstract class AbstractRule
{
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
}
