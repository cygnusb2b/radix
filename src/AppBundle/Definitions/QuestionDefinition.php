<?php

namespace AppBundle\Definitions;

use AppBundle\Question\TypeManager;

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
     * @var TypeManager
     */
    private $typeManager;

    /**
     * @param   string  $name
     * @param   string  $type
     * @throws  \InvalidArgumentException
     */
    public function __construct(TypeManager $typeManager, $name, $type)
    {
        $this->typeManager = $typeManager;
        $this->name        = trim($name);
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
        $type = $this->typeManager->getQuestionTypeFor($this->type);
        if (true === $type->supportsChoices()) {
            $this->choiceDefinitions[] = $definition;
        }
        return $this;
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
        $type            = $this->typeManager->getQuestionTypeFor($this->type);
        $this->allowHtml = true === $type->supportsHtml() ? (boolean) $bit : false;
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
        $types = $this->typeManager->getQuestionTypes();
        if (!isset($types[$type])) {
            throw new \InvalidArgumentException(sprintf('The provided question type "%s" is not valid. Valid types are "%s"', $type, implode(', ', array_keys($types))));
        }
        $this->type = $type;
        return $this;
    }
}
