<?php

namespace AppBundle\Integration\Definition;

class QuestionDefinition extends AbstractDefinition
{
    /**
     * @var QuestionChoiceDefinition
     */
    private $choices = [];

    /**
     * Constructor.
     *
     * @param   string  $name
     * @param   string  $questionType
     * @throws  \InvalidArgumentException
     */
    public function __construct($name, $questionType)
    {
        parent::__construct();
        $attributes = $this->getAttributes();
        foreach (['name', 'questionType'] as $k) {
            $value = trim($$k);
            if (empty($value)) {
                throw new \InvalidArgumentException(sprintf('The value for `%s` cannot be empty', $k));
            }
            $attributes->set($k, $value);
        }
    }

    /**
     * @param   QuestionChoiceDefinition     $definition
     * @return  self
     */
    public function addChoice(QuestionChoiceDefinition $definition)
    {
        if (false === $definition->isEmpty()) {
            $this->choices[] = $definition;
        }
        return $this;
    }

    /**
     * @return  QuestionChoiceDefinition[]
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        if (true === parent::isEmpty()) {
            return true;
        }
        return empty($this->choices);
    }
}
