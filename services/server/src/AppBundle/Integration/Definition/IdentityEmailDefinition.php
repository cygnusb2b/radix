<?php

namespace AppBundle\Integration\Definition;

use AppBundle\Utility\ModelUtility;

class IdentityEmailDefinition extends AbstractDefinition
{
    /**
     * Constructor.
     *
     * @param   string      $value      The email address value.
     * @param   string|null $identifier The identifier for the email address (optional).
     */
    public function __construct($value, $identifier = null)
    {
        parent::__construct();
        if (!empty($identifier)) {
            $this->getAttributes()->set('identifier', $identifier);
        }
        $value = ModelUtility::formatEmailAddress($value);
        $this->validateValue($value);
        $this->getAttributes()->set('value', $value);
    }

    /**
     * @param   string  $value
     * @throws  \InvalidArgumentException
     */
    private function validateValue($value)
    {
        if (false === ModelUtility::isEmailAddressValid($value)) {
            throw new \InvalidArgumentException(sprintf('The email address `%s` is invalid.', $value));
        }
    }
}
