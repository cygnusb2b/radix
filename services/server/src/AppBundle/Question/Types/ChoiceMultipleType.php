<?php

namespace AppBundle\Question\Types;

use AppBundle\Question\TypeInterface;

class ChoiceMultipleType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAnswerType()
    {
        return 'choices';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'choice-multiple';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'A list of choices with multiple answers';
    }

    /**
     * {@inheritdoc}
     */
    public function normalizeAnswer($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsChoices()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsHtml()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAnswer($value)
    {
        return true;
    }
}
