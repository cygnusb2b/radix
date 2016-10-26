<?php

namespace AppBundle\Integration\Definition;

abstract class AbstractIdentityDefinition extends AbstractDefinition
{
    /**
     * @var IdentityAddressDefinition[]
     */
    private $addresses = [];

    /**
     * @var IdentityPhoneDefinition[]
     */
    private $phones = [];

    /**
     * @param   IdentityAddressDefinition   $definition
     * @return  self
     */
    public function addAddress(IdentityAddressDefinition $definition)
    {
        if (false === $definition->isEmpty()) {
            $this->addresses[] = $definition;
        }
        return $this;
    }

    /**
     * @param   IdentityPhoneDefinition   $definition
     * @return  self
     */
    public function addPhone(IdentityPhoneDefinition $definition)
    {
        if (false === $definition->isEmpty()) {
            $this->phones[] = $definition;
        }
        return $this;
    }

    /**
     * @return  IdentityAddressDefinition[]
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @return  IdentityPhoneDefinition[]
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        if (true === parent::isEmpty()) {
            return true;
        }
        return empty($this->addresses) && empty($this->phones);
    }
}
