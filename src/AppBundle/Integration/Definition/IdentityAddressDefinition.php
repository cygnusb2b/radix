<?php

namespace AppBundle\Integration\Definition;

class IdentityAddressDefinition extends AbstractDefinition
{
    /**
     * Constructor.
     *
     * @param   string|null $identifier The identifier for the address (optional).
     */
    public function __construct($identifier = null)
    {
        parent::__construct();
        if (!empty($identifier)) {
            $this->getAttributes()->set('identifier', $identifier);
        }
    }
}
