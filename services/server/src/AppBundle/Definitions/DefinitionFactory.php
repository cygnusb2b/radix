<?php

namespace AppBundle\Definitions;

use AppBundle\Question\TypeManager;

class DefinitionFactory
{
    /**
     * @var TypeManager
     */
    private $typeManager;

    /**
     * @param   TypeManager     $typeManager
     */
    public function __construct(TypeManager $typeManager)
    {
        $this->typeManager = $typeManager;
    }

    /**
     * @param   string  $name
     * @param   string  $type
     * @return  QuestionChoiceDefinition
     */
    public function createQuestionChoiceDefinition($name, $type)
    {
        return new QuestionChoiceDefinition($this->typeManager, $name, $type);
    }

    /**
     * @param   string  $name
     * @param   string  $type
     * @return  QuestionDefinition
     */
    public function createQuestionDefinition($name, $type)
    {
        return new QuestionDefinition($this->typeManager, $name, $type);
    }
}
