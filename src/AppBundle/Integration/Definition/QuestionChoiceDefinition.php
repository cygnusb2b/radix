<?php

namespace AppBundle\Integration\Definition;

class QuestionChoiceDefinition extends AbstractDefinition
{
    /**
     * Constructor.
     *
     * @param   string  $name
     * @param   string  $choiceType
     * @throws  \InvalidArgumentException
     */
    public function __construct($name, $choiceType)
    {
        parent::__construct();
        $attributes = $this->getAttributes();
        foreach (['name', 'choiceType'] as $k) {
            $value = trim($$k);
            if (empty($value)) {
                throw new \InvalidArgumentException(sprintf('The value for `%s` cannot be empty', $k));
            }
            $attributes->set($k, $value);
        }
    }
}
