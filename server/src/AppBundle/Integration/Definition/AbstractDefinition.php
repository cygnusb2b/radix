<?php

namespace AppBundle\Integration\Definition;

use As3\Parameters\Parameters;

abstract class AbstractDefinition
{
    /**
     * @var Parameters
     */
    private $attributes;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->attributes = new Parameters();
    }

    /**
     * The definition attributes.
     *
     * @return  Parameters
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Determines if this definition is empty.
     *
     * @return  bool
     */
    public function isEmpty()
    {
        return $this->attributes->areEmpty();
    }
}
