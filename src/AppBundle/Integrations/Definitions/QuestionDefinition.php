<?php

namespace AppBundle\Integrations\Definitions;

use AppBundle\Utility\ModelUtility as RadixUtility;

class QuestionDefinition
{
    /**
     * @var bool
     */
    private $allowHtml = false;

    /**
     * @var QuestionChoiceDefinition[]
     */
    private $choiceDefinitions = [];

    /**
     * @var string|null
     */
    private $label;

    /**
     * @var string
     */
    private $name;

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
            throw new \InvalidArgumentException('The question definition name cannot be empty.');
        }
        $this->setType($type);
    }

    /**
     * @param   QuestionChoiceDefinition    $definition
     * @return  self
     */
    public function addChoiceDefinition(QuestionChoiceDefinition $definition)
    {
        if (true === $this->canAddChoices()) {
            $this->choiceDefinitions[] = $definition;
        }
        return $this;
    }

    /**
     * @return  bool
     */
    public function canAddChoices()
    {
        return 'choice-single' === $this->type || 'choice-multiple' === $this->type;
    }

    /**
     * @return  bool
     */
    public function canAllowHtml()
    {
        return 'string' === $this->type || 'textarea' === $this->type;
    }

    /**
     * @return  bool
     */
    public function getAllowHtml()
    {
        return $this->allowHtml;
    }

    /**
     * @return  QuestionChoiceDefinition[]
     */
    public function getChoiceDefinitions()
    {
        return $this->choiceDefinitions;
    }

    /**
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return  string|null
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return  string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param   bool    $bit
     * @return  self
     */
    public function setAllowHtml($bit = true)
    {
        $this->allowHtml = (boolean) $bit;
        return $this;
    }

    /**
     * @param   string  $label
     * @return  self
     */
    public function setLabel($label)
    {
        $label = trim($label);
        $this->label = empty($label) ? null : $label;
        return $this;
    }

    /**
     * @param   string  $type
     * @return  self
     * @throws  \InvalidArgumentException
     */
    private function setType($type)
    {
        $types = RadixUtility::getFormAnswerTypes();
        if (!isset($types[$type])) {
            throw new \InvalidArgumentException(sprintf('The provided question type "%s" is not valid. Valid types are "%s"', $type, implode(', ', array_keys($types))));
        }
        $this->type = $type;
        return $this;
    }
}
