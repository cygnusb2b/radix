<?php

namespace AppBundle\Integration\Definition;

class ExternalIdentityDefinition extends AbstractIdentityDefinition
{
    /**
     * @var IdentityEmailDefinition[]
     */
    private $emails = [];

    /**
     * @param   IdentityEmailDefinition     $definition
     * @return  self
     */
    public function addEmail(IdentityEmailDefinition $definition)
    {
        if (false === $definition->isEmpty()) {
            $this->emails[] = $definition;
        }
        return $this;
    }

    /**
     * @return  IdentityEmailDefinition[]
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        if (true === parent::isEmpty()) {
            return true;
        }
        return empty($this->emails);
    }
}
