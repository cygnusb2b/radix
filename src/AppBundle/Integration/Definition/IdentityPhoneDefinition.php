<?php

namespace AppBundle\Integration\Definition;

class IdentityPhoneDefinition extends AbstractDefinition
{
    /**
     * Constructor.
     *
     * @param   string      $number     The phone number.
     * @param   string|null $identifier The identifier for the phone number (optional).
     * @throws  \InvalidArgumentException
     */
    public function __construct($number, $identifier = null)
    {
        parent::__construct();

        $number = trim($number);
        if (empty($number)) {
            throw new \InvalidArgumentException('The phone number cannot be empty.');
        }
        $this->getAttributes()->set('number', $number);

        if (!empty($identifier)) {
            $this->getAttributes()->set('identifier', $identifier);
        }
    }
}
