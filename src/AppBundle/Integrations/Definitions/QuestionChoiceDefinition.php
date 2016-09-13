<?php

namespace AppBundle\Integrations\Definitions;

use AppBundle\Utility\ModelUtility as RadixUtility;

class QuestionChoiceDefinition
{
    /**
     * @var string|null
     */
    private $alternateId;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $externalId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $sequence = 0;

    /**
     * @var string
     */
    private $type;

    /**
     * @param   string  $name
     * @param   string  $type
     * @throws  \InvalidArgumentException
     */
    public function __construct($name, $type)
    {
        $this->name = trim($name);
        if (empty($this->name)) {
            throw new \InvalidArgumentException('The question choice definition name cannot be empty.');
        }
        $this->setType($type);
    }

    /**
     * @return  string|null
     */
    public function getAlternateId()
    {
        return $this->alternateId;
    }

    /**
     * @return  string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return  string|null
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return  int
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * @return  string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param   string  $alternatedId
     * @return  self
     */
    public function setAlternateId($alternateId)
    {
        $alternateId = trim($alternateId);
        $this->alternateId = '' === $alternateId ? null : $alternateId;
        return $this;
    }

    /**
     * @param   string  $description
     * @return  self
     */
    public function setDescription($description)
    {
        $description = trim($description);
        $this->description = empty($description) ? null : $description;
        return $this;
    }

    /**
     * @param   string  $externalId
     * @return  self
     */
    public function setExternalId($externalId)
    {
        $externalId = (string) $externalId;
        $this->externalId = '' === $externalId ? null : $externalId;
        return $this;
    }

    /**
     * @param   int     $sequence
     * @return  self
     */
    public function setSequence($sequence)
    {
        $this->sequence = (integer) $sequence;
        return $this;
    }

    /**
     * @param   string  $type
     * @return  self
     * @throws  \InvalidArgumentException
     */
    private function setType($type)
    {
        $types = RadixUtility::getQuestionChoiceTypes();
        if (!isset($types[$type])) {
            throw new \InvalidArgumentException(sprintf('The provided question choice type "%s" is not valid. Valid types are "%s"', $type, implode(', ', array_keys($types))));
        }
        $this->type = $type;
        return $this;
    }
}
