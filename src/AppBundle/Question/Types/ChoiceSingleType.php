<?php

namespace AppBundle\Question\Types;

use AppBundle\Question\TypeInterface;

class ChoiceSingleType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAnswerType()
    {
        return 'string';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'choice-single';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'A list of choices with a single answer';
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
}
